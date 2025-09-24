<?php

namespace App\Jobs;

use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotifyRecibosVencidosJob implements ShouldQueue
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
            Log::info('NotifyRecibosVencidosJob ejecutándose: ' . $hoy->toDateTimeString());

            // Buscar recibos vencidos que aún no han sido notificados por WhatsApp
            $recibosVencidos = Recibo::with('cliente')
                ->whereIn('estado_recibo', ['pendiente', 'vencido'])
                ->whereDate('fecha_vencimiento', '<', $hoy->toDateString())
                ->where('enviado_whatsapp', 0)
                ->whereNotNull('data_cliente')
                ->get()
                ->filter(function ($recibo) {
                    // Verificar que tenga al menos un teléfono (en data_cliente o en el cliente)
                    $telefonoDataCliente = $recibo->data_cliente['telefono'] ?? null;
                    $cliente = $recibo->cliente;

                    return ! empty($telefonoDataCliente) || ($cliente && $cliente->tieneTelefono());
                });

            Log::info('Recibos vencidos encontrados para notificar: ' . $recibosVencidos->count());

            $notificacionesEnviadas = 0;

            foreach ($recibosVencidos as $recibo) {
                try {
                    $this->enviarNotificacionVencido($recibo);

                    // Marcar como enviado por WhatsApp
                    $recibo->update(['enviado_whatsapp' => 1]);

                    $notificacionesEnviadas++;

                    Log::info("Notificación de vencido enviada para recibo: {$recibo->numero_recibo}");
                } catch (\Exception $e) {
                    Log::error("Error enviando notificación de vencido para recibo {$recibo->numero_recibo}: " . $e->getMessage());
                }
            }

            // Actualizar estado de recibos que aún están como pendientes pero ya vencieron
            $this->actualizarRecibosVencidos();

            Log::info("NotifyRecibosVencidosJob completado. Notificaciones de vencidos enviadas: {$notificacionesEnviadas}");
        } catch (\Exception $e) {
            Log::error('Error en NotifyRecibosVencidosJob: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar notificación WhatsApp al cliente por recibo vencido
     */
    private function enviarNotificacionVencido(Recibo $recibo): void
    {
        $diasVencidos = Carbon::now()->diffInDays(Carbon::parse($recibo->fecha_vencimiento));
        $diasTexto = "vencido hace {$diasVencidos} día" . ($diasVencidos > 1 ? 's' : '');

        $mensaje = $this->generarMensajeVencido($recibo, $diasTexto);

        // Obtener todos los teléfonos del cliente
        $telefonos = $this->obtenerTelefonosCliente($recibo);

        if (empty($telefonos)) {
            Log::warning("Recibo vencido {$recibo->numero_recibo} no tiene teléfonos registrados");

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
                    Log::info("Mensaje de recibo vencido enviado a {$telefono} para recibo {$recibo->numero_recibo}");
                } else {
                    Log::warning("Error enviando mensaje de recibo vencido a {$telefono} para recibo {$recibo->numero_recibo}");
                }
            } catch (\Exception $e) {
                Log::error("Excepción enviando mensaje a {$telefono}: " . $e->getMessage());
            }
        }

        if (! $enviadoAlMenosUno) {
            throw new \Exception('Error enviando mensaje de recibo vencido por WhatsApp a todos los números');
        }

        // Registrar notificación enviada
        $this->registrarNotificacionVencidoEnviada($recibo);
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
     * Generar mensaje personalizado para recibo vencido
     */
    private function generarMensajeVencido(Recibo $recibo, string $diasTexto): string
    {
        // Obtener plantilla desde la base de datos
        $plantilla = \App\Models\PlantillaMensaje::porTipo('recibo_vencido');

        if (! $plantilla) {
            // Fallback al mensaje por defecto si no existe plantilla
            $clienteNombre = $recibo->data_cliente['nombre_cliente'] ?? 'Cliente';
            $placa = $recibo->data_cobro['placa'] ?? 'Cobro general';
            $servicioNombre = $recibo->data_servicio['nombre'] ?? 'Servicio GPS';
            $periodoFacturacion = $recibo->data_cobro['periodo_facturacion'] ?? 'Mensual';

            $mensaje = "🚨 *RECIBO VENCIDO* 🚨\n\n";
            $mensaje .= "Estimado(a) *{$clienteNombre}*,\n\n";
            $mensaje .= "Su recibo está VENCIDO. Le solicitamos regularizar su situación a la brevedad:\n\n";
            $mensaje .= "📄 *Recibo:* {$recibo->numero_recibo}\n";
            $mensaje .= "🚗 *Placa:* {$placa}\n";
            $mensaje .= "🛠️ *Servicio:* {$servicioNombre}\n";
            $mensaje .= "💰 *Monto:* {$recibo->moneda} " . number_format($recibo->monto_recibo, 2) . "\n";
            $mensaje .= "📅 *Período:* {$periodoFacturacion}\n";
            $mensaje .= "⏰ *{$diasTexto}*\n\n";
            $mensaje .= "⚠️ *IMPORTANTE:* Su servicio podría ser suspendido por falta de pago.\n\n";
            $mensaje .= "Para regularizar su situación, comuníquese urgentemente con nosotros.\n\n";
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
            'fecha_vencimiento' => Carbon::parse($recibo->fecha_vencimiento)->format('d/m/Y'),
        ];

        return $plantilla->procesarMensaje($variables);
    }

    /**
     * Registrar que se envió una notificación de vencido
     */
    private function registrarNotificacionVencidoEnviada(Recibo $recibo): void
    {
        $recibo->registrarNotificacionEnviada('recibo_vencido', Carbon::now());
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
