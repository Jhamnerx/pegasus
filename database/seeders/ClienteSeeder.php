<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre_cliente' => 'Transportes El Águila S.A.C.',
                'ruc_dni' => '20123456789',
                'telefono' => '01-2345678',
                'correo_electronico' => 'contacto@transporteselagila.com',
                'direccion' => 'Av. Los Transportistas 123, Lima',
                'estado' => 'Activo',
            ],
            [
                'nombre_cliente' => 'Logística del Sur E.I.R.L.',
                'ruc_dni' => '20234567890',
                'telefono' => '054-987654',
                'correo_electronico' => 'ventas@logisticasur.com.pe',
                'direccion' => 'Jr. Comercio 456, Arequipa',
                'estado' => 'Activo',
            ],
            [
                'nombre_cliente' => 'Carga Pesada Norte S.A.',
                'ruc_dni' => '20345678901',
                'telefono' => '044-555123',
                'correo_electronico' => 'operaciones@cargapesadanorte.pe',
                'direccion' => 'Panamericana Norte Km 15, Trujillo',
                'estado' => 'Inactivo',
            ],
            [
                'nombre_cliente' => 'Distribuidora Central',
                'ruc_dni' => '20456789012',
                'telefono' => '01-9876543',
                'correo_electronico' => 'admin@distribuidoracentral.com',
                'direccion' => 'Av. Central 789, Callao',
                'estado' => 'Activo',
            ],
            [
                'nombre_cliente' => 'Transporte Rápido Express',
                'ruc_dni' => '20567890123',
                'telefono' => '01-1234567',
                'correo_electronico' => 'info@rapidoexpress.pe',
                'direccion' => 'Calle Los Pinos 321, San Juan de Lurigancho',
                'estado' => 'Activo',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
