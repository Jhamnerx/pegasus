<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('configuraciones')->insert([
            'razon_social' => 'PEGASUS S.A.C.',
            'direccion' => 'Collantes 704, ofic 101 urb Primavera',
            'telefono' => '+51 915274968',
            'email' => 'gerencia@synthesisgroup.pe',
            'moneda' => 'PEN',
            'logo' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNDY1IDEwMjQiPjxzdHlsZT4uc3QwLC5zdDF7ZmlsbDojNGE2ZGE3fTwvc3R5bGU+PHBhdGggY2xhc3M9InN0MCIgZD0iTTY0MyAyMDhoNTIzdjY0SDY0M3oiLz48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNjQzIDI0MGgzNTJWMzA0SDY0M3pNNjQzIDMwNGgzNTJWMzY4SDY0M3pNOTk1IDQzMmgxNzF2NjRIOTk1ek03MTUgNDMyaDE3MXY2NEg3MTV6TTY0MyA0OTZoMzUyVjU2MEg2NDN6TTY0MyA1NjBoNTIzdjY0SDY0M3pNNzE1IDYyNGgyNzl2NjRINzE1eiIvPjxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0zMTEgNjA2czE1OC0xMzQgMjkwIDE4YzE1MyA0Ni04MSAxMTgtODEgMTE4cy0xNTQgNzYtMjM5LTUyYy0zNi02OCAxOS0xMTEgMzAtODR6Ii8+PHBhdGggY2xhc3M9InN0MCIgZD0iTTQ4MiA1MDYgMzExIDYwNnMxMDAtNDIgMTcxLTEwMHoiLz48cGF0aCBjbGFzcz0ic3QxIiBkPSJNNjg1IDU3OXMxNTgtMTM0IDI5MCAxOGMxNTMgNDYtODEgMTE4LTgxIDExOHMtMTU0IDc2LTIzOS01MmMtMzYtNjggMTktMTExIDMwLTg0eiIvPjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04NTYgNDc5IDY4NSA1Nzlz MTAwLTQyIDE3MS0xMDB6Ii8+PHBhdGggY2xhc3M9InN0MSIgZD0iTTQ5OCA3OTBzMTU4LTEzNCAyOTAgMThjMTUzIDQ2LTgxIDExOC04MSAxMThzLTE1NCA3Ni0yMzktNTJjLTM2LTY4IDE5LTExMSAzMC04NHoiLz48cGF0aCBjbGFzcz0ic3QwIiBkPSJNNjY5IDY5MCA0OTggNzkwczEwMC00MiAxNzEtMTAweiIvPjwvc3ZnPg==',
            'metodos_pago' => json_encode([
                'Efectivo',
                'Tarjeta de crédito',
                'Transferencia bancaria',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
