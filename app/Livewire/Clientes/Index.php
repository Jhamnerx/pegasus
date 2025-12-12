<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Index extends Component
{
    use WithoutUrlPagination, WithPagination;

    #[Url]
    public $search = '';

    public function render()
    {
        $clientes = Cliente::query()
            ->when($this->search, function ($query) {
                $query->where('nombre_cliente', 'like', '%' . $this->search . '%')
                    ->orWhere('ruc_dni', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.clientes.index', [
            'clientes' => $clientes,
        ]);
    }

    #[On('clientesSaved')]
    public function refreshClientes(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->dispatch('openCreateForm');
    }

    public function openEditForm(Cliente $cliente): void
    {
        $this->dispatch('openEditForm', $cliente);
    }

    public function openModalDelete(Cliente $cliente): void
    {
        $this->dispatch('openModalDelete', cliente: $cliente);
    }

    public function openDeudaModal(Cliente $cliente): void
    {
        $this->dispatch('openDeudaModal', cliente: $cliente);
    }
}
