<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReciboDetalle extends Model
{
    use HasFactory;

    protected $table = 'recibos_detalle';

    protected $fillable = [
        'recibo_id',
        'cobro_placa_id',
        'concepto',
        'placa',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'dias_calculados',
        'monto_base',
        'factor_prorateo',
        'monto_calculado',
        'moneda',
        'observaciones',
        'es_prorrateo',
        'orden',
    ];

    protected $casts = [
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'monto_base' => 'decimal:2',
        'factor_prorateo' => 'decimal:4',
        'monto_calculado' => 'decimal:2',
        'es_prorrateo' => 'boolean',
        'dias_calculados' => 'integer',
        'orden' => 'integer',
    ];

    /**
     * Relación con Recibo
     * Una línea de detalle pertenece a un recibo
     */
    public function recibo(): BelongsTo
    {
        return $this->belongsTo(Recibo::class, 'recibo_id');
    }

    /**
     * Relación con CobroPlaca
     * Una línea de detalle puede estar asociada a una placa específica
     */
    public function cobroPlaca(): BelongsTo
    {
        return $this->belongsTo(CobroPlaca::class, 'cobro_placa_id');
    }

    /**
     * Scopes
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeConProrrateo($query)
    {
        return $query->where('es_prorrateo', true);
    }

    public function scopeSinProrrateo($query)
    {
        return $query->where('es_prorrateo', false);
    }

    /**
     * Accessors
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $descripcion = $this->concepto;

        if ($this->placa) {
            $descripcion .= " - Placa: {$this->placa}";
        }

        if ($this->es_prorrateo) {
            $descripcion .= " (Prorrateo: {$this->dias_calculados} días)";
        }

        return $descripcion;
    }

    public function getPorcentajeProrrateoAttribute(): float
    {
        return round($this->factor_prorateo * 100, 2);
    }

    /**
     * Métodos de negocio
     */
    public function esLineaProrrateo(): bool
    {
        return $this->es_prorrateo;
    }

    public function tieneDescuento(): bool
    {
        return $this->factor_prorateo < 1.0000;
    }

    public function calcularDescuento(): float
    {
        return $this->monto_base - $this->monto_calculado;
    }
}
