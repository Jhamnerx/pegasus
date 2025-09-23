<?php

namespace App\Livewire\Recibos;

use App\Models\Recibo;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Delete extends Component
{
    public ?Recibo $recibo = null;
    public bool $showModal = false;
    public string $motivoAnulacion = '';

    protected array $rules = [
        'motivoAnulacion' => 'required|string|max:500|min:10',
    ];

    protected array $messages = [
        'motivoAnulacion.required' => 'Debe indicar el motivo de la anulación.',
        'motivoAnulacion.min' => 'El motivo debe tener al menos 10 caracteres.',
        'motivoAnulacion.max' => 'El motivo no puede exceder 500 caracteres.',
    ];

    public function mount(): void
    {
        // El componente solo maneja el modal, no necesita recibo en mount
    }

    #[On('openDeleteModal')]
    public function openModal(int $reciboId): void
    {
        $this->recibo = Recibo::findOrFail($reciboId);

        // Verificar si el recibo puede ser anulado
        if ($this->recibo->estado_recibo === 'anulado') {
            session()->flash('error', 'Este recibo ya está anulado.');
            return;
        }

        $this->showModal = true;
        $this->motivoAnulacion = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->motivoAnulacion = '';
        $this->resetErrorBag();
    }

    #[On('closeModal')]
    public function handleCloseModal(): void
    {
        $this->closeModal();
    }

    public function anular(): void
    {
        if (!$this->recibo) {
            session()->flash('error', 'No se ha seleccionado ningún recibo.');
            $this->closeModal();
            return;
        }

        $this->validate();

        try {
            // Verificar si el recibo puede ser anulado
            if ($this->recibo->estado_recibo === 'anulado') {
                session()->flash('error', 'Este recibo ya está anulado.');
                $this->closeModal();
                return;
            }

            // Verificar si hay restricciones adicionales
            if ($this->recibo->estado_recibo === 'pagado' && !$this->canAnularPagado()) {
                session()->flash('error', 'No se puede anular un recibo pagado sin autorización especial.');
                $this->closeModal();
                return;
            }

            // Anular el recibo usando el método del modelo
            $this->recibo->anular($this->motivoAnulacion);

            $this->dispatch('recibo-deleted');

            session()->flash('success', 'Recibo anulado correctamente.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al anular el recibo: ' . $e->getMessage());
            $this->closeModal();
        }
    }

    private function canAnularPagado(): bool
    {
        // Aquí puedes agregar lógica adicional para verificar si el usuario
        // tiene permisos para anular recibos pagados
        // Por ejemplo, verificar roles, permisos especiales, etc.

        // Por ahora, solo permitir si es un administrador o similar
        $user = Auth::user();
        return $user && method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('supervisor'));
    }

    public function getWarningMessage(): string
    {
        if (!$this->recibo) {
            return 'No se ha seleccionado ningún recibo.';
        }

        if ($this->recibo->estado_recibo === 'pagado') {
            return '⚠️ ATENCIÓN: Este recibo está marcado como PAGADO. La anulación requerirá procesamiento adicional de la reversión del pago.';
        }

        if ($this->recibo->estado_recibo === 'vencido') {
            return '⚠️ NOTA: Este recibo está vencido. La anulación eliminará cualquier penalidad o interés aplicado.';
        }

        return 'Esta acción anulará el recibo y no se podrá deshacer.';
    }

    public function getButtonText(): string
    {
        if (!$this->recibo) {
            return 'Anular Recibo';
        }

        return $this->recibo->estado_recibo === 'pagado' ? 'Anular Recibo Pagado' : 'Anular Recibo';
    }

    public function render()
    {
        return view('livewire.recibos.delete', [
            'warningMessage' => $this->getWarningMessage(),
            'buttonText' => $this->getButtonText(),
        ]);
    }
}
