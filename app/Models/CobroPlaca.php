<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CobroPlaca extends Model
{
    use HasFactory;

    protected $table = 'cobro_placas';

    protected $fillable = [
        'cobro_id',
        'placa',
        'monto_calculado',
        'dias_prorrateados',
        'factor_prorateo',
        'observaciones',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'monto_calculado' => 'decimal:2',
        'factor_prorateo' => 'decimal:4',
        'dias_prorrateados' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Cobro
     * Una placa pertenece a un cobro
     */
    public function cobro(): BelongsTo
    {
        return $this->belongsTo(Cobro::class, 'cobro_id');
    }

    /**
     * Relación con ReciboDetalle
     * Una placa puede tener múltiples líneas de detalle en recibos
     */
    public function recibos(): HasMany
    {
        return $this->hasMany(ReciboDetalle::class, 'cobro_placa_id');
    }

    /**
     * Accessors
     */
    public function getMontoFinalAttribute(): float
    {
        return $this->monto_calculado;
    }

    public function getTieneProrateoAttribute(): bool
    {
        return $this->factor_prorateo !== null && $this->factor_prorateo != 1.0000;
    }

    /**
     * Métodos de negocio
     */

    /**
     * Calcula el prorrateo para esta placa específica
     */
    public function calcularProrrateo(): array
    {
        if (!$this->cobro) {
            return [
                'dias_prorrateados' => 0,
                'factor_prorateo' => 1.0,
                'monto_calculado' => $this->monto_calculado ?? 0,
            ];
        }

        // Fechas del cobro general
        $fechaInicioCobro = Carbon::parse($this->cobro->fecha_inicio_periodo);
        $fechaFinCobro = Carbon::parse($this->cobro->fecha_fin_periodo);

        // Fechas específicas de la placa (si no están definidas, usar las del cobro)
        $fechaInicioPlaca = $this->fecha_inicio ? Carbon::parse($this->fecha_inicio) : $fechaInicioCobro;
        $fechaFinPlaca = $this->fecha_fin ? Carbon::parse($this->fecha_fin) : $fechaFinCobro;

        // Asegurar que las fechas de la placa estén dentro del rango del cobro
        $fechaInicioPlaca = $fechaInicioPlaca->max($fechaInicioCobro);
        $fechaFinPlaca = $fechaFinPlaca->min($fechaFinCobro);

        // Calcular días
        $diasTotales = $fechaInicioCobro->diffInDays($fechaFinCobro) + 1;
        $diasPlaca = $fechaInicioPlaca->diffInDays($fechaFinPlaca) + 1;

        // Calcular factor de prorrateo
        $factorProrrateo = $diasTotales > 0 ? round($diasPlaca / $diasTotales, 4) : 1.0;

        // Calcular monto
        $montoBase = $this->cobro->monto_unitario ?? $this->cobro->monto_base ?? 0;
        $montoCalculado = round($montoBase * $factorProrrateo, 2);

        return [
            'dias_prorrateados' => $diasPlaca,
            'factor_prorateo' => $factorProrrateo,
            'monto_calculado' => $montoCalculado,
        ];
    }

    /**
     * Aplica el cálculo de prorrateo y actualiza los campos
     */
    public function aplicarProrrateo(): void
    {
        $resultado = $this->calcularProrrateo();

        $this->update([
            'dias_prorrateados' => $resultado['dias_prorrateados'],
            'factor_prorateo' => $resultado['factor_prorateo'],
            'monto_calculado' => $resultado['monto_calculado'],
        ]);
    }

    public function calcularMonto(): void
    {
        // El monto ya está calculado según el factor de prorateo en la migración
        // Este método puede usarse para recalcular si es necesario
        $montoBase = $this->cobro ? $this->cobro->monto_unitario ?? $this->cobro->monto_base : $this->monto_calculado;

        if ($this->factor_prorateo && $this->factor_prorateo != 1.0000) {
            $montoFinal = $montoBase * $this->factor_prorateo;
        } else {
            $montoFinal = $montoBase;
        }

        $this->update(['monto_calculado' => round($montoFinal, 2)]);
    }

    /**
     * Scopes
     */
    public function scopeConProrateo($query)
    {
        return $query->whereNotNull('factor_prorateo')
            ->where('factor_prorateo', '!=', 1.0000);
    }

    public function scopeSinProrateo($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('factor_prorateo')
                ->orWhere('factor_prorateo', 1.0000);
        });
    }

    public function scopePorCobro($query, $cobroId)
    {
        return $query->where('cobro_id', $cobroId);
    }
}
