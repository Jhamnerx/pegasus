<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $operadorRol = Rol::where('nombre_rol', 'Operador')->first();
        $cobradorRol = Rol::where('nombre_rol', 'Cobrador')->first();
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

        // Usuario operador
        User::create([
            'name' => 'operador',
            'email' => 'operador@pegasus.com',
            'password' => Hash::make('password'),
            'username' => 'operador',
            'rol_id' => $operadorRol?->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Usuario cobrador
        User::create([
            'name' => 'cobrador',
            'email' => 'cobrador@pegasus.com',
            'password' => Hash::make('password'),
            'username' => 'cobrador',
            'rol_id' => $cobradorRol?->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Usuario de solo lectura
        User::create([
            'name' => 'consulta',
            'email' => 'consulta@pegasus.com',
            'password' => Hash::make('password'),
            'username' => 'consulta',
            'rol_id' => $consultaRol?->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        echo "✅ Usuarios creados:\n";
        echo "   Admin: admin@pegasus.com / admin (contraseña: password)\n";
        echo "   Operador: operador@pegasus.com / operador (contraseña: password)\n";
        echo "   Cobrador: cobrador@pegasus.com / cobrador (contraseña: password)\n";
        echo "   Consulta: consulta@pegasus.com / consulta (contraseña: password)\n";
    }
}
