<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Form extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?Servicio $servicio = null;

    public bool $isEditing = false;

    // Propiedades del formulario
    public string $nombre_servicio = '';

    public string $descripcion = '';

    public string $precio_base = '';

    public bool $activo = true;

    protected function rules(): array
    {
        $servicioId = $this->servicio?->id;

        return [
            'nombre_servicio' => 'required|string|max:255|unique:servicios,nombre_servicio,'.$servicioId,
            'descripcion' => 'nullable|string|max:1000',
            'precio_base' => 'required|numeric|min:0|max:99999.99',
            'activo' => 'required|boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre_servicio.required' => 'El nombre del servicio es obligatorio.',
            'nombre_servicio.unique' => 'Ya existe un servicio con este nombre.',
            'nombre_servicio.max' => 'El nombre del servicio no puede exceder 255 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            'precio_base.required' => 'El precio base es obligatorio.',
            'precio_base.numeric' => 'El precio base debe ser un número válido.',
            'precio_base.min' => 'El precio base debe ser mayor o igual a 0.',
            'precio_base.max' => 'El precio base no puede exceder 99,999.99.',
            'activo.required' => 'El estado del servicio es obligatorio.',
        ];
    }

    #[On('openCreateForm')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isOpen = true;
    }

    #[On('openEditForm')]
    public function openEdit(Servicio $servicio): void
    {
        $this->resetForm();
        $this->servicio = $servicio;
        $this->isEditing = true;
        $this->fillForm();
        $this->isOpen = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'nombre_servicio' => $this->nombre_servicio,
                'descripcion' => $this->descripcion,
                'precio_base' => $this->precio_base,
                'activo' => $this->activo,
            ];

            if ($this->isEditing && $this->servicio) {
                $this->servicio->update($data);
                $this->notification()->success(
                    'Servicio actualizado',
                    'El servicio ha sido actualizado correctamente.'
                );
            } else {
                Servicio::create($data);
                $this->notification()->success(
                    'Servicio creado',
                    'El servicio ha sido creado correctamente.'
                );
            }

            $this->closeModal();
            $this->dispatch('serviciosSaved');
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'Ha ocurrido un error al guardar el servicio. Por favor, inténtelo de nuevo.'
            );
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->servicio = null;
        $this->nombre_servicio = '';
        $this->descripcion = '';
        $this->precio_base = '';
        $this->activo = true;
        $this->resetValidation();
    }

    private function fillForm(): void
    {
        if ($this->servicio) {
            $this->nombre_servicio = $this->servicio->nombre_servicio;
            $this->descripcion = $this->servicio->descripcion ?? '';
            $this->precio_base = (string) $this->servicio->precio_base;
            $this->activo = $this->servicio->activo;
        }
    }

    public function render()
    {
        return view('livewire.servicios.form');
    }
}
