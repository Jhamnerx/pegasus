<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaMensaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'nombre',
        'asunto',
        'mensaje',
        'variables_disponibles',
        'activo',
    ];

    protected $casts = [
        'variables_disponibles' => 'array',
        'activo' => 'boolean',
    ];

    /**
     * Obtener plantilla por tipo
     */
    public static function porTipo(string $tipo): ?self
    {
        return self::where('tipo', $tipo)->where('activo', true)->first();
    }

    /**
     * Procesar mensaje reemplazando variables
     */
    public function procesarMensaje(array $variables = []): string
    {
        $mensaje = $this->mensaje;

        foreach ($variables as $variable => $valor) {
            $mensaje = str_replace("{{$variable}}", $valor, $mensaje);
        }

        return $mensaje;
    }

    /**
     * Obtener variables disponibles para este tipo de plantilla
     */
    public function getVariablesDisponiblesAttribute(): array
    {
        return match ($this->tipo) {
            'creacion_recibo' => [
                'cliente_nombre' => 'Nombre del cliente',
                'numero_recibo' => 'Número del recibo',
                'servicio_nombre' => 'Nombre del servicio',
                'monto_recibo' => 'Monto del recibo',
                'fecha_vencimiento' => 'Fecha de vencimiento',
                'placas_recibo' => 'Placas incluidas en el recibo',
                'url_publica' => 'URL pública del recibo',
                'empresa_nombre' => 'Nombre de la empresa',
            ],
            'recordatorio_pago' => [
                'cliente_nombre' => 'Nombre del cliente',
                'numero_recibo' => 'Número del recibo',
                'placas_recibo' => 'Placas incluidas en el recibo',
                'servicio_nombre' => 'Nombre del servicio',
                'monto_recibo' => 'Monto del recibo',
                'dias_texto' => 'Días para/desde vencimiento',
                'periodo_facturacion' => 'Período de facturación',
                'empresa_nombre' => 'Nombre de la empresa',
            ],
            default => []
        };
    }
}
