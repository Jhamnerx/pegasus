<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles existentes
        $adminRol = Rol::where('nombre_rol', 'Administrador')->first();

        $consultaRol = Rol::where('nombre_rol', 'Consulta')->first();

        // Usuario administrador
        User::create([
            'name' => 'admin',
            'email' => 'gerencia@synthesisgroup.pe',
            'password' => Hash::make('password'),
            'username' => 'admin',
            'rol_id' => $adminRol?->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);
    }
}
