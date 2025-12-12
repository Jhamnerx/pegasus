<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Empresa;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class EmpresaWhatsAppTest extends TestCase
{
    /** @test */
    public function puede_renderizar_el_componente_con_seccion_whatsapp(): void
    {
        $user = User::first();

        if (! $user) {
            $this->markTestSkipped('No hay usuarios en la base de datos para este test');
        }

        $this->actingAs($user)
            ->get(route('settings.empresa'))
            ->assertStatus(200)
            ->assertSee('Prueba de WhatsApp')
            ->assertSee('Probar WhatsApp');
    }

    /** @test */
    public function componente_livewire_tiene_propiedades_whatsapp(): void
    {
        $user = User::first();

        if (! $user) {
            $this->markTestSkipped('No hay usuarios en la base de datos para este test');
        }

        $component = Livewire::actingAs($user)->test(Empresa::class);

        $component->assertSet('isOpenModalWhatsapp', false);
        $component->assertSet('numeroWhatsapp', '');
        $component->assertSet('mensajeWhatsapp', '');
    }
}
