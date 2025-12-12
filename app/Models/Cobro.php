<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cobro extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'descripcion_servicio_personalizado',
        'monto_base',
        'cantidad_placas',
        'monto_unitario',
        'tiene_prorateo',
        'periodo_facturacion',
        'moneda',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'dias_para_vencimiento',
        'estado',
        'fecha_procesamiento',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'fecha_procesamiento' => 'datetime',
        'monto_base' => 'decimal:2',
        'monto_unitario' => 'decimal:2',
        'tiene_prorateo' => 'boolean',
        'cantidad_placas' => 'integer',
        'dias_para_vencimiento' => 'integer',
    ];

    /**
     * Relación con Cliente
     * Un cobro pertenece a un cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación con Servicio
     * Un cobro puede estar asociado a un servicio
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Relación con CobroPlaca
     * Un cobro puede tener muchas placas
     */
    public function cobroPlacas(): HasMany
    {
        return $this->hasMany(CobroPlaca::class, 'cobro_id');
    }

    /**
     * Relación con Recibo
     * Un cobro puede generar muchos recibos
     */
    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class, 'cobro_id');
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeProcesados($query)
    {
        return $query->where('estado', 'procesado');
    }

    public function scopeAnulados($query)
    {
        return $query->where('estado', 'anulado');
    }

    public function scopeParaProcesar($query)
    {
        return $query->where('estado', 'activo')
            ->whereDate('fecha_fin_periodo', '<=', now());
    }

    /**
     * Accessors y Mutators
     */
    public function getMontoTotalAttribute(): float
    {
        if ($this->cobroPlacas->count() > 0) {
            return $this->cobroPlacas->sum('monto_calculado');
        }

        return $this->monto_base;
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'activo' => 'Pendiente de Procesar',
            'procesado' => 'Recibos Generados',
            'anulado' => 'Anulado',
            default => 'Estado Desconocido'
        };
    }

    public function getPlacasListaAttribute(): string
    {
        if ($this->cobroPlacas->count() > 0) {
            return $this->cobroPlacas->pluck('placa')->join(', ');
        }

        return 'Cobro general';
    }

    /**
     * Métodos de negocio
     */
    public function puedeSerProcesado(): bool
    {
        return $this->estado === 'activo' &&
            Carbon::parse($this->fecha_fin_periodo)->lte(now());
    }

    public function calcularFechaVencimientoRecibos(): Carbon
    {
        return now()->addDays($this->dias_para_vencimiento);
    }

    public function marcarComoProcesado(): void
    {
        $this->update([
            'estado' => 'procesado',
            'fecha_procesamiento' => now(),
        ]);
    }

    public function anular(?string $motivo = null): void
    {
        $this->update([
            'estado' => 'anulado',
            'notas' => $this->notas . "\n\nAnulado: " . now()->format('Y-m-d H:i:s') .
                ($motivo ? " - Motivo: {$motivo}" : ''),
        ]);
    }

    /**
     * Obtener descripción del servicio
     */
    public function getDescripcionServicio(): string
    {
        return $this->servicio ?
            $this->servicio->nombre_servicio :
            $this->descripcion_servicio_personalizado ?? 'Servicio no especificado';
    }

    /**
     * Verificar si tiene placas vencidas que necesitan renovación
     */
    public function tienePlacasParaRenovar(): bool
    {
        return $this->cobroPlacas()
            ->whereDate('fecha_fin', '<', now())
            ->whereHas('recibos', function ($query) {
                $query->whereHas('recibo', function ($reciboQuery) {
                    $reciboQuery->whereIn('estado_recibo', ['pendiente', 'pagado', 'vencido']);
                });
            })
            ->exists();
    }

    /**
     * Obtener placas activas (no vencidas)
     */
    public function placasActivas()
    {
        return $this->cobroPlacas()
            ->whereDate('fecha_fin', '>=', now());
    }

    /**
     * Obtener placas vencidas
     */
    public function placasVencidas()
    {
        return $this->cobroPlacas()
            ->whereDate('fecha_fin', '<', now());
    }
}
