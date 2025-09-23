<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            // Servicios principales activos
            [
                'id' => 13,
                'nombre_servicio' => 'Plan 20',
                'descripcion' => 'Servicio de Rastreo básico',
                'precio_base' => 20.00,
                'activo' => true,
            ],
            [
                'id' => 14,
                'nombre_servicio' => 'Plan 25',
                'descripcion' => 'Servicio de monitoreo',
                'precio_base' => 25.00,
                'activo' => true,
            ],
            [
                'id' => 15,
                'nombre_servicio' => 'Plan 35',
                'descripcion' => 'Servicio de monitoreo corporativo',
                'precio_base' => 35.00,
                'activo' => true,
            ],
            [
                'id' => 16,
                'nombre_servicio' => 'Plan 40',
                'descripcion' => 'Plan de servicio de rastreo GPS plan sutran',
                'precio_base' => 40.00,
                'activo' => true,
            ],
            [
                'id' => 17,
                'nombre_servicio' => 'Plan 60',
                'descripcion' => 'Servicio de monitoreo GPS doble operador',
                'precio_base' => 60.00,
                'activo' => true,
            ],
            [
                'id' => 18,
                'nombre_servicio' => 'Plan 50',
                'descripcion' => 'Plan de rastreo GPS doble operador básico',
                'precio_base' => 50.00,
                'activo' => true,
            ],
            [
                'id' => 19,
                'nombre_servicio' => 'Servicio Anual 350',
                'descripcion' => 'Servicio monitoreo vehicular GPS plan anual',
                'precio_base' => 350.00,
                'activo' => true,
            ],
            [
                'id' => 20,
                'nombre_servicio' => 'Plan Anual 300',
                'descripcion' => 'Servicio monitoreo vehicular GPS',
                'precio_base' => 300.00,
                'activo' => true,
            ],
            [
                'id' => 21,
                'nombre_servicio' => 'Plan Anual 250',
                'descripcion' => 'Plan básico de monitoreo vehicular GPS',
                'precio_base' => 250.00,
                'activo' => true,
            ],
            [
                'id' => 22,
                'nombre_servicio' => 'Plan Anual 400',
                'descripcion' => 'Plan de rastreo satelital GPS plan anual',
                'precio_base' => 400.00,
                'activo' => true,
            ],
            [
                'id' => 23,
                'nombre_servicio' => 'Plan Anual 500',
                'descripcion' => 'Plan anual de rastreo vehicular GPS',
                'precio_base' => 500.00,
                'activo' => true,
            ],
            [
                'id' => 24,
                'nombre_servicio' => 'Plan 500',
                'descripcion' => 'Plan anual de rastreo vehicular satelital GPS',
                'precio_base' => 500.00,
                'activo' => true,
            ],
            [
                'id' => 25,
                'nombre_servicio' => 'Plan 30',
                'descripcion' => 'Monitoreo satelital básico',
                'precio_base' => 30.00,
                'activo' => true,
            ],
            [
                'id' => 26,
                'nombre_servicio' => 'Plan 380',
                'descripcion' => 'Plan de rastreo satelital vehicular GPS',
                'precio_base' => 380.00,
                'activo' => true,
            ],
            [
                'id' => 27,
                'nombre_servicio' => 'Plan Semestral 120',
                'descripcion' => 'Monitoreo satelital GPS vehicular plan semestral',
                'precio_base' => 120.00,
                'activo' => true,
            ],
            [
                'id' => 28,
                'nombre_servicio' => 'Chip Cuy 7',
                'descripcion' => 'Servicio de chip básico',
                'precio_base' => 7.00,
                'activo' => true,
            ],
            // Servicios legacy (inactivos)
            [
                'id' => 1,
                'nombre_servicio' => 'Monitoreo GPS',
                'descripcion' => 'Servicio de monitoreo GPS para vehículos y flotas (Legacy)',
                'precio_base' => 800.00,
                'activo' => false,
            ],
            [
                'id' => 2,
                'nombre_servicio' => 'Mantenimiento',
                'descripcion' => 'Servicio de mantenimiento técnico preventivo y correctivo (Legacy)',
                'precio_base' => 1200.00,
                'activo' => false,
            ],
            [
                'id' => 3,
                'nombre_servicio' => 'Consultoría',
                'descripcion' => 'Servicio de asesoría y consultoría empresarial (Legacy)',
                'precio_base' => 2000.00,
                'activo' => false,
            ],
        ];

        foreach ($servicios as $servicio) {
            DB::table('servicios')->updateOrInsert(
                ['id' => $servicio['id']],
                array_merge($servicio, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
