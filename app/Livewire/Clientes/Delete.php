<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Delete extends Component
{
    use WireUiActions;

    public ?Cliente $cliente = null;

    public bool $showModal = false;

    #[On('openModalDelete')]
    public function openModal(Cliente $cliente): void
    {
        $this->cliente = $cliente;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->cliente = null;
    }

    public function confirmDelete(): void
    {
        if (! $this->cliente) {
            return;
        }

        try {
            // Verificar si el cliente tiene cobros asociados
            if ($this->cliente->cobros()->exists()) {
                $this->notification()->error(
                    'No se puede eliminar',
                    'Este cliente tiene cobros asociados. No es posible eliminarlo.'
                );

                return;
            }

            $nombreCliente = $this->cliente->nombre_cliente;
            $this->cliente->delete();

            $this->notification()->success(
                'Cliente eliminado',
                "El cliente \"{$nombreCliente}\" ha sido eliminado correctamente."
            );

            $this->closeModal();
            $this->dispatch('clientesSaved');
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'Ha ocurrido un error al eliminar el cliente. Por favor, inténtelo de nuevo.'
            );
        }
    }

    public function render()
    {
        return view('livewire.clientes.delete');
    }
}
