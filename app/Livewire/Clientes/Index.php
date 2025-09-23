<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;

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
            ->paginate(10);

        return view('livewire.clientes.index', [
            'clientes' => $clientes
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
}
