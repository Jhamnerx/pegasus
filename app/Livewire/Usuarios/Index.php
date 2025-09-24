<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
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

    protected $queryString = ['search'];

    /**
     * Abrir formulario para crear usuario
     */
    public function openCreateForm(): void
    {
        $this->dispatch('open-form-modal', userId: null);
    }

    /**
     * Abrir formulario para editar usuario
     */
    public function openEditForm(int $userId): void
    {
        $this->dispatch('open-form-modal', userId: $userId);
    }

    /**
     * Abrir modal para cambiar estado del usuario
     */
    public function openStatusModal(int $userId): void
    {
        $this->dispatch('open-status-modal', userId: $userId);
    }

    /**
     * Resetear paginación cuando se busca
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Refrescar datos cuando se guarda un usuario
     */
    #[On('user-saved')]
    public function refreshData(): void
    {
        $this->resetPage();
        $this->render();
    }

    public function render()
    {
        $usuarios = User::with('rol')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->orWhereHas('rol', function ($rolQuery) {
                        $rolQuery->where('nombre_rol', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.usuarios.index', compact('usuarios'));
    }
}
