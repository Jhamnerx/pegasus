<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            ConfiguracionesSeeder::class,
            RolesSeeder::class,
            UsersSeeder::class,
            ServiciosSeeder::class,
            ClientesSeeder::class,
            PlantillaMensajeSeeder::class,
        ]);
    }
}
