<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'razon_social',
        'direccion',
        'telefono',
        'email',
        'moneda',
        'logo',
        'metodos_pago',
    ];

    /**
     * Obtener la configuración de empresa (primera fila)
     */
    public static function obtenerEmpresa()
    {
        return self::first();
    }

    /**
     * Crear o actualizar la configuración de empresa
     */
    public static function actualizarEmpresa(array $datos)
    {
        $configuracion = self::first();

        if ($configuracion) {
            $configuracion->update($datos);
        } else {
            self::create($datos);
        }

        return $configuracion ?? self::first();
    }

    /**
     * Obtener el logo de la empresa
     */
    public static function obtenerLogo(): ?string
    {
        $configuracion = self::first();

        return $configuracion?->logo;
    }

    /**
     * Obtener la razón social de la empresa
     */
    public static function obtenerRazonSocial(): string
    {
        $configuracion = self::first();

        return $configuracion?->razon_social ?? config('app.name', 'PEGASUS');
    }
}
