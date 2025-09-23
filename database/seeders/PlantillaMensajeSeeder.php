<?php

namespace Database\Seeders;

use App\Models\PlantillaMensaje;
use Illuminate\Database\Seeder;

class PlantillaMensajeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plantilla para creación de recibo
        PlantillaMensaje::updateOrCreate(
            ['tipo' => 'creacion_recibo'],
            [
                'nombre' => 'Notificación de Nuevo Recibo',
                'asunto' => 'Nuevo Recibo - {numero_recibo}',
                'mensaje' => "📋 *NUEVO RECIBO* 📋\n\n" .
                    "Estimado(a) *{cliente_nombre}*,\n\n" .
                    "📄 *Recibo:* {numero_recibo}\n" .
                    "🛠️ *Servicio:* {servicio_nombre}\n" .
                    "💰 *Monto:* PEN {monto_recibo}\n" .
                    "📅 *Vencimiento:* {fecha_vencimiento}\n" .
                    "🚗 *Placas:* {placas_recibo}\n\n" .
                    "📎 PDF adjunto.\n" .
                    "🔗 *Ver recibo online:* {url_publica}\n\n" .
                    "📞 *{empresa_nombre}*\n" .
                    "_Mensaje automático_",
                'activo' => true,
            ]
        );

        // Plantilla para recordatorio de pago
        PlantillaMensaje::updateOrCreate(
            ['tipo' => 'recordatorio_pago'],
            [
                'nombre' => 'Recordatorio de Pago',
                'asunto' => 'Recordatorio de Pago - {numero_recibo}',
                'mensaje' => "📋 *RECORDATORIO DE PAGO* 📋\n\n" .
                    "Estimado(a) *{cliente_nombre}*,\n\n" .
                    "Le recordamos que tiene un recibo pendiente de pago:\n\n" .
                    "📄 *Recibo:* {numero_recibo}\n" .
                    "🚗 *Placas:* {placas_recibo}\n" .
                    "🛠️ *Servicio:* {servicio_nombre}\n" .
                    "💰 *Monto:* PEN {monto_recibo}\n" .
                    "📅 *Período:* {periodo_facturacion}\n" .
                    "⏰ *{dias_texto}*\n\n" .
                    "Para realizar el pago, comuníquese con nosotros.\n\n" .
                    "📞 *{empresa_nombre}*\n" .
                    "Sistema GPS y Cobranzas\n\n" .
                    "_Este es un mensaje automático del sistema._",
                'activo' => true,
            ]
        );
    }
}
