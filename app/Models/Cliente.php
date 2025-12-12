<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory;
    use softDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre_cliente',
        'ruc_dni',
        'telefono',
        'telefono_1',
        'telefono_2',
        'telefono_3',
        'correo_electronico',
        'direccion',
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Cobro
     * Un cliente tiene muchos cobros
     */
    public function cobros()
    {
        return $this->hasMany(Cobro::class, 'cliente_id');
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Scope para clientes inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'Inactivo');
    }

    /**
     * Obtener todos los teléfonos del cliente (sin valores nulos)
     */
    public function getTelefonosAttribute(): array
    {
        return collect([
            $this->telefono,
            $this->telefono_1,
            $this->telefono_2,
            $this->telefono_3,
        ])->filter()->values()->toArray();
    }

    /**
     * Obtener el teléfono principal (el primer teléfono disponible)
     */
    public function getTelefonoPrincipalAttribute(): ?string
    {
        return $this->telefono ?: $this->telefono_1 ?: $this->telefono_2 ?: $this->telefono_3;
    }

    /**
     * Verificar si el cliente tiene al menos un teléfono registrado
     */
    public function tieneTelefono(): bool
    {
        return ! empty($this->telefonos);
    }

    /**
     * Obtener todos los recibos no pagados del cliente
     */
    public function recibosNoPagados()
    {
        return Recibo::where('cliente_id', $this->id)
            ->whereIn('estado_recibo', ['pendiente', 'vencido'])
            ->orderBy('fecha_vencimiento', 'asc');
    }

    /**
     * Obtener el total de deuda del cliente
     */
    public function getTotalDeudaAttribute(): float
    {
        return (float) $this->recibosNoPagados()->sum('monto_recibo');
    }

    /**
     * Obtener la cantidad de recibos pendientes de pago
     */
    public function getCantidadRecibosPendientesAttribute(): int
    {
        return $this->recibosNoPagados()->count();
    }
}
