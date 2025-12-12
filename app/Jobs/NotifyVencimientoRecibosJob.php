<?php

namespace App\Jobs;

use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotifyVencimientoRecibosJob implements ShouldQueue
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
    public function handle(): void
    {
        try {
            $hoy = Carbon::now();
            Log::info('NotifyVencimientoRecibosJob ejecutándose: '.$hoy->toDateTimeString());

            // Buscar recibos que necesitan notificación hoy
            $recibosPendientes = Recibo::with('cliente')
                ->where('estado_recibo', 'pendiente')
                ->whereDate('proxima_notificacion', '<=', $hoy->toDateString())
                ->whereNotNull('data_cliente')
                ->get()
                ->filter(function ($recibo) {
                    // Verificar que tenga al menos un teléfono (en data_cliente o en el cliente)
                    $telefonoDataCliente = $recibo->data_cliente['telefono'] ?? null;
                    $cliente = $recibo->cliente;

                    return ! empty($telefonoDataCliente) || ($cliente && $cliente->tieneTelefono());
                });

            Log::info('Recibos encontrados para notificar: '.$recibosPendientes->count());

            $notificacionesEnviadas = 0;

            foreach ($recibosPendientes as $recibo) {
                try {
                    $this->enviarNotificacion($recibo);
                    $this->actualizarProximaNotificacion($recibo);
                    $notificacionesEnviadas++;

                    Log::info("Notificación enviada para recibo: {$recibo->numero_recibo}");
                } catch (\Exception $e) {
                    Log::error("Error enviando notificación para recibo {$recibo->numero_recibo}: ".$e->getMessage());
                }
            }

            // Actualizar estado de recibos vencidos
            $this->actualizarRecibosVencidos();

            Log::info("NotifyVencimientoRecibosJob completado. Notificaciones enviadas: {$notificacionesEnviadas}");
        } catch (\Exception $e) {
            Log::error('Error en NotifyVencimientoRecibosJob: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar notificación WhatsApp al cliente
     */
    private function enviarNotificacion(Recibo $recibo): void
    {
        $diasParaVencimiento = Carbon::parse($recibo->fecha_vencimiento)->diffInDays(Carbon::now(), false);

        // Determinar el tipo de mensaje
        if ($diasParaVencimiento > 0) {
            $tipoMensaje = 'vencido';
            $diasTexto = "vencido hace {$diasParaVencimiento} días";
        } elseif ($diasParaVencimiento == 0) {
            $tipoMensaje = 'vence_hoy';
            $diasTexto = 'vence HOY';
        } else {
            $tipoMensaje = 'proximo_vencimiento';
            $diasParaVencimiento = abs($diasParaVencimiento);
            $diasTexto = "vence en {$diasParaVencimiento} días";
        }

        $mensaje = $this->generarMensaje($recibo, $diasTexto, $tipoMensaje);

        // Obtener todos los teléfonos del cliente
        $telefonos = $this->obtenerTelefonosCliente($recibo);

        if (empty($telefonos)) {
            Log::warning("Recibo {$recibo->numero_recibo} no tiene teléfonos registrados");

            return;
        }

        $whatsAppService = app('whatsapp');
        $enviadoAlMenosUno = false;

        // Enviar mensaje a todos los teléfonos
        foreach ($telefonos as $telefono) {
            try {
                $enviado = $whatsAppService->sendMessage($telefono, $mensaje);

                if ($enviado) {
                    $enviadoAlMenosUno = true;
                    Log::info("Mensaje de vencimiento enviado a {$telefono} para recibo {$recibo->numero_recibo}");
                } else {
                    Log::warning("Error enviando mensaje de vencimiento a {$telefono} para recibo {$recibo->numero_recibo}");
                }
            } catch (\Exception $e) {
                Log::error("Excepción enviando mensaje a {$telefono}: ".$e->getMessage());
            }
        }

        if (! $enviadoAlMenosUno) {
            throw new \Exception('Error enviando mensaje de recordatorio por WhatsApp a todos los números');
        }

        // Registrar notificación enviada
        $this->registrarNotificacionEnviada($recibo, $tipoMensaje);
    }

    /**
     * Obtener todos los teléfonos del cliente para el recibo
     */
    private function obtenerTelefonosCliente(Recibo $recibo): array
    {
        $telefonos = [];

        // Primero intentar del campo JSON del recibo
        if (isset($recibo->data_cliente['telefono']) && ! empty($recibo->data_cliente['telefono'])) {
            $telefonos[] = $recibo->data_cliente['telefono'];
        }

        // Luego del modelo Cliente si está disponible
        if ($recibo->cliente && $recibo->cliente->tieneTelefono()) {
            $telefonosCliente = $recibo->cliente->telefonos;

            // Agregar solo los teléfonos que no estén ya en el array
            foreach ($telefonosCliente as $telefono) {
                if (! in_array($telefono, $telefonos)) {
                    $telefonos[] = $telefono;
                }
            }
        }

        return array_filter($telefonos); // Remover valores vacíos
    }

    /**
     * Generar mensaje personalizado según el tipo de vencimiento
     */
    private function generarMensaje(Recibo $recibo, string $diasTexto, string $tipoMensaje): string
    {
        // Obtener plantilla desde la base de datos
        $plantilla = \App\Models\PlantillaMensaje::porTipo('recordatorio_pago');

        if (! $plantilla) {
            // Fallback al mensaje original si no existe plantilla
            $emoji = match ($tipoMensaje) {
                'vencido' => '🚨',
                'vence_hoy' => '⚠️',
                'proximo_vencimiento' => '📋',
                default => '📋'
            };

            $saludo = match ($tipoMensaje) {
                'vencido' => 'RECIBO VENCIDO',
                'vence_hoy' => 'RECIBO VENCE HOY',
                'proximo_vencimiento' => 'RECORDATORIO DE PAGO',
                default => 'RECORDATORIO DE PAGO'
            };

            // Extraer datos de los campos JSON
            $clienteNombre = $recibo->data_cliente['nombre_cliente'] ?? 'Cliente';
            $placa = $recibo->data_cobro['placa'] ?? 'Cobro general';
            $servicioNombre = $recibo->data_servicio['nombre'] ?? 'Servicio GPS';
            $periodoFacturacion = $recibo->data_cobro['periodo_facturacion'] ?? 'Mensual';

            $mensaje = "{$emoji} *{$saludo}* {$emoji}\n\n";
            $mensaje .= "Estimado(a) *{$clienteNombre}*,\n\n";
            $mensaje .= "Le recordamos que tiene un recibo pendiente de pago:\n\n";
            $mensaje .= "📄 *Recibo:* {$recibo->numero_recibo}\n";
            $mensaje .= "🚗 *Placa:* {$placa}\n";
            $mensaje .= "🛠️ *Servicio:* {$servicioNombre}\n";
            $mensaje .= "💰 *Monto:* {$recibo->moneda} ".number_format($recibo->monto_recibo, 2)."\n";
            $mensaje .= "📅 *Período:* {$periodoFacturacion}\n";
            $mensaje .= "⏰ *{$diasTexto}*\n\n";

            if ($tipoMensaje === 'vencido') {
                $mensaje .= "⚠️ Su recibo está VENCIDO. Por favor, póngase en contacto con nosotros para regularizar su situación.\n\n";
            } else {
                $mensaje .= "Para realizar el pago, comuníquese con nosotros.\n\n";
            }

            $mensaje .= "📞 *PEGASUS S.A.C.*\n";
            $mensaje .= "Sistema GPS y Cobranzas\n\n";
            $mensaje .= '_Este es un mensaje automático del sistema._';

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
            'placas_recibo' => $placasRecibo ?: 'No especificadas',
            'servicio_nombre' => $recibo->data_servicio['nombre'] ?? 'Servicio GPS',
            'monto_recibo' => number_format($recibo->monto_recibo, 2),
            'dias_texto' => $diasTexto,
            'periodo_facturacion' => $recibo->data_cobro['periodo_facturacion'] ?? 'Mensual',
            'empresa_nombre' => \App\Models\Configuracion::obtenerRazonSocial(),
        ];

        return $plantilla->procesarMensaje($variables);
    }

    /**
     * Registrar que se envió una notificación
     */
    private function registrarNotificacionEnviada(Recibo $recibo, string $tipoMensaje): void
    {
        $recibo->registrarNotificacionEnviada($tipoMensaje, Carbon::now());
    }

    /**
     * Actualizar la próxima fecha de notificación
     */
    private function actualizarProximaNotificacion(Recibo $recibo): void
    {
        $alertDays = explode(',', env('ALERT_DAYS', '7,3,1'));
        $diasOrdenados = collect($alertDays)->map(fn ($d) => (int) $d)->sort()->reverse()->values();

        $fechaVencimiento = Carbon::parse($recibo->fecha_vencimiento);
        $hoy = Carbon::now();

        // Encontrar el próximo día de alerta
        $proximaNotificacion = null;

        foreach ($diasOrdenados as $dias) {
            $fechaAlerta = $fechaVencimiento->copy()->subDays($dias);

            if ($fechaAlerta > $hoy) {
                $proximaNotificacion = $fechaAlerta;
                break;
            }
        }

        // Si no hay más alertas antes del vencimiento, programar para después del vencimiento
        if (! $proximaNotificacion) {
            if ($hoy <= $fechaVencimiento) {
                // Vence hoy o en el futuro - programar para el día del vencimiento
                $proximaNotificacion = $fechaVencimiento;
            } else {
                // Ya vencido - programar para el siguiente día (alertas diarias de vencidos)
                $proximaNotificacion = $hoy->copy()->addDay();
            }
        }

        $recibo->update([
            'proxima_notificacion' => $proximaNotificacion,
        ]);
    }

    /**
     * Actualizar estado de recibos que ya vencieron
     */
    private function actualizarRecibosVencidos(): void
    {
        $hoy = Carbon::now()->toDateString();

        $recibosVencidos = Recibo::where('estado_recibo', 'pendiente')
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->update(['estado_recibo' => 'vencido']);

        if ($recibosVencidos > 0) {
            Log::info("Recibos marcados como vencidos: {$recibosVencidos}");
        }
    }
}
