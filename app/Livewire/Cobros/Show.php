<?php

namespace App\Livewire\Cobros;

use App\Models\Cobro;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public bool $isOpen = false;

    public ?Cobro $cobro = null;

    public function render()
    {
        return view('livewire.cobros.show');
    }

    #[On('openShowModal')]
    public function openShow(Cobro $cobro): void
    {
        $this->cobro = $cobro->load(['cliente', 'servicio', 'cobroPlacas']);
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->cobro = null;
    }
}
