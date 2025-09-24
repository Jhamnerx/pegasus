<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Status extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?int $userId = null;

    public ?User $user = null;

    #[On('open-status-modal')]
    public function openModal(int $userId): void
    {
        $this->userId = $userId;
        $this->user = User::with('rol')->findOrFail($userId);
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->isOpen = false;
    }

    public function toggleStatus(): void
    {
        if (! $this->user) {
            $this->notification()->error('Usuario no encontrado');

            return;
        }

        // Prevenir que un usuario se desactive a sí mismo
        if ($this->user->id === Auth::id()) {
            $this->notification()->error(
                'Acción no permitida',
                'No puedes cambiar tu propio estado'
            );

            return;
        }

        try {
            $nuevoEstado = ! $this->user->activo;

            $this->user->update(['activo' => $nuevoEstado]);

            $mensaje = $nuevoEstado ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
            $this->notification()->success($mensaje);

            $this->dispatch('user-saved');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'Ocurrió un error al cambiar el estado del usuario: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.usuarios.status');
    }
}
