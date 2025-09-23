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
}
