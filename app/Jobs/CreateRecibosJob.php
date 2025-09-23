<?php

namespace App\Jobs;

use App\Models\Cobro;
use App\Models\CobroPlaca;
use App\Models\Recibo;
use App\Models\ReciboDetalle;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateRecibosJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): bool
    {
        try {
            // Obtener días de alerta
            $alertDays = explode(',', env('ALERT_DAYS', '7,3,1'));
            $diasAnticipacion = max(array_map('intval', $alertDays));

            // Calcular fecha específica: placas que vencen exactamente en X días
            $fechaObjetivo = Carbon::now()->addDays($diasAnticipacion)->toDateString();

            Log::info("CreateRecibosJob: Buscando placas que vencen exactamente el {$fechaObjetivo} (en {$diasAnticipacion} días)");

            // Buscar placas que necesitan recibos
            $placasVencen = CobroPlaca::with(['cobro.cliente', 'cobro.servicio'])
                ->whereHas('cobro', function ($query) {
                    $query->where('estado', 'activo');
                })
                ->whereDate('fecha_fin', $fechaObjetivo)  // Solo placas que vencen en la fecha objetivo
                ->whereDoesntHave('recibos', function ($query) {
                    // Verificar que no exista un recibo_detalle para este período específico
                    $query->whereHas('recibo', function ($reciboQuery) {
                        $reciboQuery->whereIn('estado_recibo', ['pendiente', 'pagado']);
                    })
                        ->whereColumn('fecha_inicio_periodo', 'cobro_placas.fecha_inicio')
                        ->whereColumn('fecha_fin_periodo', 'cobro_placas.fecha_fin');
                })
                ->get();
            if ($placasVencen->isEmpty()) {
                Log::info("CreateRecibosJob: No hay placas que necesiten recibos en el período especificado");
                return false;
            }

            Log::info("CreateRecibosJob: Placas encontradas que necesitan recibos: " . json_encode($placasVencen->pluck('placa')));

            // Log de placas encontradas para debugging
            foreach ($placasVencen as $placa) {
                Log::info("Placa {$placa->placa} del cobro {$placa->cobro_id} vence el {$placa->fecha_fin}");
            }

            // Agrupar placas por cobro
            $recibosCreados = 0;
            $placasPorCobro = $placasVencen->groupBy('cobro_id');

            foreach ($placasPorCobro as $cobroId => $placas) {
                DB::beginTransaction();
                try {
                    $cobro = $placas->first()->cobro;
                    $this->generarRecibo($cobro, $placas);
                    $recibosCreados++;
                    DB::commit();

                    Log::info("Recibo creado para cobro {$cobroId} con " . $placas->count() . " placas");
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Error creando recibo para cobro {$cobroId}: " . $e->getMessage());
                }
            }

            Log::info("CreateRecibosJob completado. Recibos creados: {$recibosCreados}");
            return $recibosCreados > 0;
        } catch (\Exception $e) {
            Log::error("Error en CreateRecibosJob: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar recibo para un cobro con sus placas que vencen
     */
    private function generarRecibo(Cobro $cobro, $placas): void
    {
        // Crear recibo principal
        $recibo = Recibo::create([
            'numero_recibo' => $this->generarNumeroRecibo(),
            'cobro_id' => $cobro->id,
            'cliente_id' => $cobro->cliente_id,
            'servicio_id' => $cobro->servicio_id,
            'monto_recibo' => 0, // Se calculará después
            'fecha_emision' => Carbon::now()->toDateString(),
            'fecha_vencimiento' => Carbon::now()->addDays($cobro->dias_para_vencimiento)->toDateString(),
            'estado_recibo' => 'pendiente',
            'cantidad_placas' => $placas->count(),
            'moneda' => $cobro->moneda ?? 'PEN',
            'data_cliente' => [
                'nombre_cliente' => $cobro->cliente->nombre_cliente,
                'ruc_dni' => $cobro->cliente->ruc_dni,
                'telefono' => $cobro->cliente->telefono,
                'correo_electronico' => $cobro->cliente->correo_electronico,
                'direccion' => $cobro->cliente->direccion,
            ],
            'data_servicio' => [
                'nombre' => $cobro->getDescripcionServicio(),
                'descripcion' => $cobro->servicio?->descripcion,
                'precio_base' => $cobro->servicio?->precio_base ?? $cobro->monto_base,
            ],
            'data_cobro' => [
                'concepto' => $cobro->getDescripcionServicio(),
                'fecha_inicio_periodo' => $placas->min('fecha_inicio') ?? $cobro->fecha_inicio_periodo,
                'fecha_fin_periodo' => $placas->max('fecha_fin') ?? $cobro->fecha_fin_periodo,
                'monto_base' => $cobro->monto_base,
                'placas_incluidas' => $placas->pluck('placa')->toArray(),
            ],
            'proxima_notificacion' => $this->calcularProximaNotificacion(),
        ]);

        // Crear detalles para cada placa
        $montoTotal = 0;
        foreach ($placas as $placa) {
            ReciboDetalle::create([
                'recibo_id' => $recibo->id,
                'cobro_placa_id' => $placa->id,
                'concepto' => "Servicio GPS - Placa {$placa->placa}",
                'placa' => $placa->placa,
                'fecha_inicio_periodo' => $placa->fecha_inicio ?? $cobro->fecha_inicio_periodo,
                'fecha_fin_periodo' => $placa->fecha_fin ?? $cobro->fecha_fin_periodo,
                'dias_calculados' => $placa->dias_prorrateados ?? 30,
                'monto_base' => $cobro->monto_base,
                'factor_prorateo' => $placa->factor_prorateo ?? 1.0000,
                'monto_calculado' => $placa->monto_calculado,
                'moneda' => $cobro->moneda ?? 'PEN',
                'es_prorrateo' => ($placa->factor_prorateo ?? 1.0000) != 1.0000,
                'orden' => 1,
            ]);

            $montoTotal += $placa->monto_calculado;
        }

        // Actualizar monto total del recibo
        $recibo->update(['monto_recibo' => $montoTotal]);

        $cobro->estado = 'procesado';
        $cobro->save();
        // Enviar PDF por WhatsApp
        $this->enviarWhatsApp($recibo);
    }

    /**
     * Calcular próxima notificación
     */
    private function calcularProximaNotificacion(): Carbon
    {
        $alertDays = explode(',', env('ALERT_DAYS', '7,3,1'));
        $maxDias = max(array_map('intval', $alertDays));
        return Carbon::now()->addDays($maxDias);
    }

    /**
     * Enviar PDF por WhatsApp
     */
    private function enviarWhatsApp(Recibo $recibo): void
    {
        try {
            $whatsAppService = app('whatsapp');

            if (!$whatsAppService->isConfigured()) {
                Log::warning("WhatsApp no configurado para recibo: {$recibo->numero_recibo}");
                return;
            }

            $telefono = $recibo->data_cliente['telefono'] ?? null;
            if (!$telefono) {
                Log::warning("Sin teléfono para recibo: {$recibo->numero_recibo}");
                return;
            }

            $pdfUrl = $whatsAppService->generatePdfUrl($recibo->uuid);

            // Generar URL pública del recibo usando UUID
            $urlPublica = url("/recibo/{$recibo->uuid}");
            $mensaje = $this->generarMensaje($recibo, $urlPublica);

            // 1. Primero enviar el mensaje de texto
            $mensajeEnviado = $whatsAppService->sendMessage($telefono, $mensaje);

            if (!$mensajeEnviado) {
                Log::error("Error enviando mensaje de texto para recibo: {$recibo->numero_recibo}");
                return;
            }

            Log::info("Mensaje de texto enviado para recibo: {$recibo->numero_recibo}");

            // 2. Luego enviar el PDF por separado
            $pdfEnviado = $whatsAppService->sendMedia($telefono, "📄 Recibo adjunto", $pdfUrl, 'pdf');

            if ($pdfEnviado) {
                Log::info("PDF enviado por WhatsApp: {$recibo->numero_recibo}");
            } else {
                Log::error("Error enviando PDF: {$recibo->numero_recibo}");
            }
        } catch (\Exception $e) {
            Log::error("Excepción WhatsApp para recibo {$recibo->numero_recibo}: " . $e->getMessage());
        }
    }

    /**
     * Generar mensaje para WhatsApp
     */
    private function generarMensaje(Recibo $recibo, string $urlPublica): string
    {
        // Obtener plantilla desde la base de datos
        $plantilla = \App\Models\PlantillaMensaje::porTipo('creacion_recibo');

        if (!$plantilla) {
            // Fallback al mensaje original si no existe plantilla
            $cliente = $recibo->data_cliente['nombre_cliente'] ?? 'Cliente';
            $servicio = $recibo->data_servicio['nombre'] ?? 'Servicio GPS';
            $vencimiento = Carbon::parse($recibo->fecha_vencimiento)->format('d/m/Y');

            $mensaje = "📋 *NUEVO RECIBO* 📋\n\n";
            $mensaje .= "Estimado(a) *{$cliente}*,\n\n";
            $mensaje .= "📄 *Recibo:* {$recibo->numero_recibo}\n";
            $mensaje .= "🛠️ *Servicio:* {$servicio}\n";
            $mensaje .= "💰 *Monto:* {$recibo->moneda} " . number_format($recibo->monto_recibo, 2) . "\n";
            $mensaje .= "📅 *Vencimiento:* {$vencimiento}\n";

            if ($recibo->cantidad_placas > 1) {
                $mensaje .= "🚗 *Placas:* {$recibo->cantidad_placas}\n";
            }

            $mensaje .= "\n📎 PDF adjunto.\n";
            $mensaje .= "🔗 *Ver recibo online:* {$urlPublica}\n\n";
            $mensaje .= "📞 *PEGASUS S.A.C.*\n";
            $mensaje .= "_Mensaje automático_";

            return $mensaje;
        }

        // Generar placas del recibo
        $placasRecibo = '';
        $dataCobro = $recibo->data_cobro ?? [];
        if (isset($dataCobro['placas_incluidas']) && is_array($dataCobro['placas_incluidas'])) {
            $placasRecibo = implode(', ', $dataCobro['placas_incluidas']);
        }

        // Usar plantilla con variables
        $variables = [
            'cliente_nombre' => $recibo->data_cliente['nombre_cliente'] ?? 'Cliente',
            'numero_recibo' => $recibo->numero_recibo,
            'servicio_nombre' => $recibo->data_servicio['nombre'] ?? 'Servicio GPS',
            'monto_recibo' => number_format($recibo->monto_recibo, 2),
            'fecha_vencimiento' => Carbon::parse($recibo->fecha_vencimiento)->format('d/m/Y'),
            'placas_recibo' => $placasRecibo ?: 'No especificadas',
            'url_publica' => $urlPublica,
            'empresa_nombre' => \App\Models\Configuracion::obtenerRazonSocial(),
        ];

        return $plantilla->procesarMensaje($variables);
    }

    /**
     * Generar número de recibo único
     */
    private function generarNumeroRecibo(): string
    {
        $prefix = 'REC';
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $ultimoRecibo = Recibo::where('numero_recibo', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('numero_recibo', 'desc')
            ->first();

        if ($ultimoRecibo) {
            $ultimoNumero = (int) substr($ultimoRecibo->numero_recibo, -6);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }

        return sprintf("%s-%s%s%06d", $prefix, $year, $month, $nuevoNumero);
    }
}
