<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Recibo extends Model
{
    use HasFactory;

    protected $table = 'recibos';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($recibo) {
            if (empty($recibo->uuid)) {
                $recibo->uuid = Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'numero_recibo',
        'cobro_id',
        'cobro_placa_id',
        'cliente_id',
        'servicio_id',
        'placa',
        'monto_recibo',
        'fecha_emision',
        'fecha_vencimiento',
        'estado_recibo',
        'fecha_pago',
        'metodo_pago',
        'numero_referencia',
        'monto_pagado',
        'data_cliente',
        'data_servicio',
        'data_cobro',
        'cantidad_placas',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'periodo_facturacion',
        'moneda',
        'observaciones',
        'notificaciones_enviadas',
        'proxima_notificacion',
        'usuario_generador_id',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'monto_recibo' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo' => 'date',
        'fecha_generacion' => 'datetime',
        'proxima_notificacion' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'notificaciones_enviadas' => 'array',
        'data_cliente' => 'array',
        'data_servicio' => 'array',
        'data_cobro' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function cobro(): BelongsTo
    {
        return $this->belongsTo(Cobro::class, 'cobro_id');
    }

    public function cobroPlaca(): BelongsTo
    {
        return $this->belongsTo(CobroPlaca::class, 'cobro_placa_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Relación con ReciboDetalle
     * Un recibo puede tener muchas líneas de detalle
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(ReciboDetalle::class, 'recibo_id')->ordenados();
    }

    /**
     * Scopes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_recibo', 'pendiente');
    }

    public function scopePagados($query)
    {
        return $query->where('estado_recibo', 'pagado');
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
            ->where('estado_recibo', 'pendiente');
    }

    public function scopeProximosAVencer($query, int $dias)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->where('fecha_vencimiento', '>', now())
            ->where('estado_recibo', 'pendiente');
    }

    /**
     * Accessors
     */
    public function getNumeroReciboFormateadoAttribute(): string
    {
        return str_pad($this->numero_recibo, 8, '0', STR_PAD_LEFT);
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado_recibo) {
            'pendiente' => 'Pendiente de Pago',
            'pagado' => 'Pagado',
            'vencido' => 'Vencido',
            'anulado' => 'Anulado',
            default => 'Estado Desconocido'
        };
    }

    public function getEstaVencidoAttribute(): bool
    {
        return $this->fecha_vencimiento < now() && $this->estado_recibo === 'pendiente';
    }

    public function getDiasParaVencimientoAttribute(): int
    {
        if ($this->estado_recibo !== 'pendiente') {
            return 0;
        }

        return max(0, now()->diffInDays($this->fecha_vencimiento, false));
    }

    // Accessors para datos JSON
    public function getDataClienteAttribute(): ?array
    {
        return $this->attributes['data_cliente'] ? json_decode($this->attributes['data_cliente'], true) : null;
    }

    public function getDataServicioAttribute(): ?array
    {
        return $this->attributes['data_servicio'] ? json_decode($this->attributes['data_servicio'], true) : null;
    }

    public function getDataCobroAttribute(): ?array
    {
        return $this->attributes['data_cobro'] ? json_decode($this->attributes['data_cobro'], true) : null;
    }

    // Accessors de conveniencia para acceder a campos específicos
    public function getClienteNombreAttribute(): ?string
    {
        return $this->dataCliente['nombre_cliente'] ?? $this->dataCliente['nombre'] ?? null;
    }

    public function getClienteDocumentoAttribute(): ?string
    {
        return $this->dataCliente['ruc_dni'] ?? $this->dataCliente['documento'] ?? null;
    }

    public function getClienteTelefonoAttribute(): ?string
    {
        return $this->dataCliente['telefono'] ?? null;
    }

    public function getClienteEmailAttribute(): ?string
    {
        return $this->dataCliente['correo_electronico'] ?? $this->dataCliente['email'] ?? null;
    }

    public function getClienteDireccionAttribute(): ?string
    {
        return $this->dataCliente['direccion'] ?? null;
    }

    public function getServicioNombreAttribute(): ?string
    {
        return $this->dataServicio['nombre_servicio'] ?? $this->dataServicio['nombre'] ?? null;
    }

    public function getServicioDescripcionAttribute(): ?string
    {
        return $this->dataServicio['descripcion'] ?? null;
    }

    public function getPlacaAttribute(): ?string
    {
        return $this->dataCobro['placa'] ?? null;
    }

    public function getFechaInicioPeriodoAttribute(): ?Carbon
    {
        $fecha = $this->dataCobro['fecha_inicio_periodo'] ?? null;

        return $fecha ? Carbon::parse($fecha) : null;
    }

    public function getFechaFinPeriodoAttribute(): ?Carbon
    {
        $fecha = $this->dataCobro['fecha_fin_periodo'] ?? null;

        return $fecha ? Carbon::parse($fecha) : null;
    }

    public function getPeriodoFacturacionAttribute(): ?string
    {
        return $this->dataCobro['periodo_facturacion'] ?? null;
    }

    public function getFactorProrateoAttribute(): float
    {
        return floatval($this->dataCobro['factor_prorateo'] ?? 1.0);
    }

    /**
     * Métodos de negocio
     */

    /**
     * Obtener el monto total calculado desde las líneas de detalle
     */
    public function calcularMontoTotal(): float
    {
        return $this->detalles()->sum('monto_calculado');
    }

    /**
     * Verificar si el recibo tiene líneas con prorrateo
     */
    public function tieneProrrateo(): bool
    {
        return $this->detalles()->where('es_prorrateo', true)->exists();
    }

    /**
     * Obtener resumen de placas del recibo
     */
    public function getResumenPlacas(): array
    {
        return $this->detalles()
            ->whereNotNull('placa')
            ->pluck('placa')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Métodos de negocio originales
     */
    public function marcarComoPagado(array $datosPago): void
    {
        $this->update([
            'estado_recibo' => 'pagado',
            'fecha_pago' => $datosPago['fecha_pago'] ?? now(),
            'metodo_pago' => $datosPago['metodo_pago'] ?? null,
            'numero_referencia' => $datosPago['numero_referencia'] ?? null,
            'monto_pagado' => $datosPago['monto_pagado'] ?? $this->monto_recibo,
            'observaciones' => $this->observaciones."\nPago registrado: ".now()->format('Y-m-d H:i:s'),
            'proxima_notificacion' => null, // Cancelar notificaciones futuras
        ]);
    }

    public function anular(?string $motivo = null): void
    {
        $this->update([
            'estado_recibo' => 'anulado',
            'fecha_anulacion' => now(),
            'motivo_anulacion' => $motivo,
            'observaciones' => $this->observaciones."\nAnulado: ".now()->format('Y-m-d H:i:s').
                ($motivo ? " - Motivo: {$motivo}" : ''),
        ]);
    }

    /**
     * Crear snapshots JSON de los datos relacionados
     */
    public function crearSnapshotCliente(): void
    {
        if ($this->cliente) {
            $this->data_cliente = [
                'id' => $this->cliente->id,
                'nombre_cliente' => $this->cliente->nombre_cliente,
                'ruc_dni' => $this->cliente->ruc_dni,
                'telefono' => $this->cliente->telefono,
                'correo_electronico' => $this->cliente->correo_electronico,
                'direccion' => $this->cliente->direccion,
                'estado' => $this->cliente->estado,
            ];
        }
    }

    public function crearSnapshotServicio(): void
    {
        if ($this->servicio) {
            $this->data_servicio = [
                'id' => $this->servicio->id,
                'nombre_servicio' => $this->servicio->nombre_servicio,
                'descripcion' => $this->servicio->descripcion,
                'precio_base' => $this->servicio->precio_base,
                'activo' => $this->servicio->activo,
            ];
        }
    }

    public function crearSnapshotCobro(): void
    {
        if ($this->cobro) {
            $this->data_cobro = [
                'id' => $this->cobro->id,
                'placa' => $this->cobro->placa,
                'monto_total' => $this->cobro->monto_total,
                'fecha_inicio_periodo' => $this->cobro->fecha_inicio_periodo?->format('Y-m-d'),
                'fecha_fin_periodo' => $this->cobro->fecha_fin_periodo?->format('Y-m-d'),
                'periodo_facturacion' => $this->cobro->periodo_facturacion,
                'factor_prorateo' => $this->cobro->factor_prorateo ?? 1.0,
                'estado' => $this->cobro->estado,
            ];
        }
    }

    public function crearSnapshots(): void
    {
        $this->crearSnapshotCliente();
        $this->crearSnapshotServicio();
        $this->crearSnapshotCobro();
        $this->save();
    }

    public function registrarNotificacionEnviada(string $tipo, Carbon $fechaEnviada): void
    {
        $notificaciones = $this->notificaciones_enviadas ?? [];
        $notificaciones[] = [
            'tipo' => $tipo,
            'fecha_enviada' => $fechaEnviada->toISOString(),
            'dias_antes_vencimiento' => $this->diasParaVencimiento,
        ];

        $this->update([
            'notificaciones_enviadas' => $notificaciones,
            'proxima_notificacion' => $this->calcularProximaNotificacion(),
        ]);
    }

    public function necesitaNotificacion(): bool
    {
        return $this->estado_recibo === 'pendiente' &&
            $this->proxima_notificacion &&
            $this->proxima_notificacion <= now();
    }

    private function calcularProximaNotificacion(): ?Carbon
    {
        $alertDays = explode(',', env('ALERT_DAYS', '7,3,1'));
        $diasEnviados = collect($this->notificaciones_enviadas ?? [])
            ->pluck('dias_antes_vencimiento')
            ->toArray();

        foreach ($alertDays as $dias) {
            $dias = (int) $dias;
            if (! in_array($dias, $diasEnviados)) {
                $fechaNotificacion = $this->fecha_vencimiento->subDays($dias);
                if ($fechaNotificacion > now()) {
                    return $fechaNotificacion;
                }
            }
        }

        return null;
    }

    /**
     * Para compatibilidad con el código anterior
     */
    public function getMontoAttribute(): float
    {
        return $this->monto_recibo;
    }

    public function getEstadoPagoAttribute(): string
    {
        return $this->estado_recibo;
    }

    public function necesitaAlerta(int $dias): bool
    {
        if ($this->estado_recibo !== 'pendiente') {
            return false;
        }

        $diasRestantes = now()->diffInDays($this->fecha_vencimiento, false);

        // Si ya vencio, no necesita alerta
        if ($diasRestantes < 0) {
            return false;
        }

        // Si los días restantes son menores o iguales a los días de alerta
        if ($diasRestantes > $dias) {
            return false;
        }

        // Verificar si ya se envió la alerta para estos días
        $notificaciones = $this->notificaciones_enviadas ?? [];
        foreach ($notificaciones as $notif) {
            if (isset($notif['dias_antes_vencimiento']) && $notif['dias_antes_vencimiento'] == $dias) {
                return false;
            }
        }

        return true;
    }

    public function marcarAlertaEnviada(int $dias): void
    {
        $notificaciones = $this->notificaciones_enviadas ?? [];
        $notificaciones[] = [
            'tipo' => "alerta_{$dias}_dias",
            'fecha_enviada' => now()->toISOString(),
            'dias_antes_vencimiento' => $dias,
        ];

        $this->update([
            'notificaciones_enviadas' => $notificaciones,
        ]);
    }

    /**
     * Determinar si el recibo es consolidado (múltiples placas)
     */
    public function esConsolidado(): bool
    {
        return $this->cantidad_placas > 1;
    }
}
