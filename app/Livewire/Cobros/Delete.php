<?php

namespace App\Livewire\Cobros;

use App\Models\Cobro;
use Livewire\Attributes\On;
use Livewire\Component;

class Delete extends Component
{
    public bool $isOpen = false;
    public ?Cobro $cobro = null;

    #[On('openDeleteModal')]
    public function openModal(int $cobroId): void
    {
        $this->cobro = Cobro::with(['cliente', 'servicio'])->find($cobroId);
        $this->isOpen = true;
    }

    public function delete(): void
    {
        if ($this->cobro) {
            try {
                $this->cobro->delete();
                session()->flash('message', 'Cobro eliminado correctamente.');
                $this->dispatch('cobroDeleted');
                $this->closeModal();
            } catch (\Exception $e) {
                session()->flash('error', 'Ha ocurrido un error al eliminar el cobro: ' . $e->getMessage());
            }
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->cobro = null;
    }

    public function render()
    {
        return view('livewire.cobros.delete');
    }
}
