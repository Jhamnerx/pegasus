<?php

namespace Tests\Feature\Livewire\Clientes;

use App\Livewire\Clientes\DeudaCliente;
use App\Models\Cliente;
use App\Models\Recibo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeudaClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_se_abre_correctamente(): void
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($user)
            ->test(DeudaCliente::class)
            ->call('openModal', $cliente)
            ->assertSet('isOpen', true)
            ->assertSet('cliente.id', $cliente->id);
    }

    public function test_muestra_recibos_no_pagados_del_cliente(): void
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        // Crear recibos con data_cliente
        Recibo::factory()->create([
            'cliente_id' => $cliente->id,
            'estado_recibo' => 'pendiente',
            'monto_recibo' => 100.00,
            'data_cliente' => [
                'id' => $cliente->id,
                'nombre_cliente' => $cliente->nombre_cliente,
            ],
        ]);

        Recibo::factory()->create([
            'cliente_id' => $cliente->id,
            'estado_recibo' => 'pendiente',
            'monto_recibo' => 200.00,
            'data_cliente' => [
                'id' => $cliente->id,
                'nombre_cliente' => $cliente->nombre_cliente,
            ],
        ]);

        // Recibo pagado - no debe aparecer
        Recibo::factory()->create([
            'cliente_id' => $cliente->id,
            'estado_recibo' => 'pagado',
            'data_cliente' => [
                'id' => $cliente->id,
                'nombre_cliente' => $cliente->nombre_cliente,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(DeudaCliente::class)
            ->call('openModal', $cliente)
            ->assertSet('totalDeuda', 300.00)
            ->assertCount('recibos', 2);
    }

    public function test_cierra_modal_correctamente(): void
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($user)
            ->test(DeudaCliente::class)
            ->call('openModal', $cliente)
            ->call('closeModal')
            ->assertSet('isOpen', false)
            ->assertSet('cliente', null);
    }

    public function test_muestra_mensaje_sin_deudas_cuando_no_hay_recibos(): void
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($user)
            ->test(DeudaCliente::class)
            ->call('openModal', $cliente)
            ->assertSet('totalDeuda', 0)
            ->assertCount('recibos', 0);
    }
}
