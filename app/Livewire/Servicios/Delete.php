<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public bool $isOpen = false;

    public ?Servicio $servicio = null;

    #[On('openDeleteModal')]
    public function openModal(int $servicioId): void
    {
        $this->servicio = Servicio::find($servicioId);
        $this->isOpen = true;
    }

    public function delete(): void
    {
        if ($this->servicio) {
            try {
                // Verificar si el servicio tiene cobros asociados
                if ($this->servicio->cobros()->exists()) {
                    session()->flash('error', 'No se puede eliminar el servicio porque tiene cobros asociados.');
                    $this->closeModal();

                    return;
                }

                $this->servicio->delete();
                session()->flash('message', 'Servicio eliminado correctamente.');
                $this->dispatch('servicioDeleted');
                $this->closeModal();
            } catch (\Exception $e) {
                session()->flash('error', 'Ha ocurrido un error al eliminar el servicio.');
            }
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->servicio = null;
    }

    public function render()
    {
        return view('livewire.servicios.delete');
    }
}
