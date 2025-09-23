<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar roles iniciales
        $roles = [
            [
                'id' => 1,
                'nombre_rol' => 'Administrador',
                'descripcion' => 'Acceso completo al sistema, puede gestionar usuarios, configuraciones y todos los módulos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre_rol' => 'Operador',
                'descripcion' => 'Puede gestionar cobros, clientes y generar reportes. No puede modificar configuraciones del sistema.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre_rol' => 'Consulta',
                'descripcion' => 'Solo puede consultar información, generar reportes y ver estadísticas. Sin permisos de edición.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['id' => $rol['id']],
                $rol
            );
        }
    }
}
