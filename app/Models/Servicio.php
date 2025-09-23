<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre_servicio',
        'descripcion',
        'precio_base',
        'activo',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Relación con Cobro
     * Un servicio puede estar en muchos cobros
     */
    public function cobros()
    {
        return $this->hasMany(Cobro::class, 'servicio_id');
    }

    /**
     * Scope para servicios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    /**
     * Scope para servicios inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('activo', 0);
    }
}
