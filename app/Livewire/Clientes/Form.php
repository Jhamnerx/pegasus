<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Form extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?Cliente $cliente = null;

    public bool $isEditing = false;

    // Propiedades del formulario
    public string $nombre_cliente = '';

    public string $ruc_dni = '';

    public string $telefono = '';

    public string $telefono_1 = '';

    public string $telefono_2 = '';

    public string $telefono_3 = '';

    public string $correo_electronico = '';

    public string $direccion = '';

    public string $estado = 'Activo';

    protected function rules(): array
    {
        $clienteId = $this->cliente ? $this->cliente->id : null;

        return [
            'nombre_cliente' => 'required|string|max:255',
            'ruc_dni' => 'required|string|max:20|unique:clientes,ruc_dni,' . $clienteId,
            'telefono' => 'nullable|string|max:20',
            'telefono_1' => 'nullable|string|max:20',
            'telefono_2' => 'nullable|string|max:20',
            'telefono_3' => 'nullable|string|max:20',
            'correo_electronico' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'estado' => 'required|in:Activo,Inactivo',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nombre_cliente' => 'nombre del cliente',
            'ruc_dni' => 'RUC/DNI',
            'telefono' => 'teléfono principal',
            'telefono_1' => 'teléfono 1',
            'telefono_2' => 'teléfono 2',
            'telefono_3' => 'teléfono 3',
            'correo_electronico' => 'correo electrónico',
            'direccion' => 'dirección',
            'estado' => 'estado del cliente',
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
    public function openEdit(Cliente $cliente): void
    {
        $this->resetForm();
        $this->cliente = $cliente;
        $this->isEditing = true;
        $this->fillForm();
        $this->isOpen = true;
    }

    private function resetForm(): void
    {
        $this->cliente = null;
        $this->nombre_cliente = '';
        $this->ruc_dni = '';
        $this->telefono = '';
        $this->telefono_1 = '';
        $this->telefono_2 = '';
        $this->telefono_3 = '';
        $this->correo_electronico = '';
        $this->direccion = '';
        $this->estado = 'Activo';
        $this->resetValidation();
    }

    private function fillForm(): void
    {
        if ($this->cliente) {
            $this->nombre_cliente = $this->cliente->nombre_cliente;
            $this->ruc_dni = $this->cliente->ruc_dni;
            $this->telefono = $this->cliente->telefono ?? '';
            $this->telefono_1 = $this->cliente->telefono_1 ?? '';
            $this->telefono_2 = $this->cliente->telefono_2 ?? '';
            $this->telefono_3 = $this->cliente->telefono_3 ?? '';
            $this->correo_electronico = $this->cliente->correo_electronico ?? '';
            $this->direccion = $this->cliente->direccion ?? '';
            $this->estado = $this->cliente->estado;
        }
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'nombre_cliente' => $this->nombre_cliente,
                'ruc_dni' => $this->ruc_dni,
                'telefono' => $this->telefono,
                'telefono_1' => $this->telefono_1,
                'telefono_2' => $this->telefono_2,
                'telefono_3' => $this->telefono_3,
                'correo_electronico' => $this->correo_electronico,
                'direccion' => $this->direccion,
                'estado' => $this->estado,
            ];

            if ($this->isEditing && $this->cliente) {
                $this->cliente->update($data);
                $this->notification()->success(
                    'Cliente actualizado',
                    'El cliente ha sido actualizado correctamente.'
                );
            } else {
                Cliente::create($data);
                $this->notification()->success(
                    'Cliente creado',
                    'El cliente ha sido creado correctamente.'
                );
            }

            $this->closeModal();
            $this->dispatch('clientesSaved');
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'Ha ocurrido un error al guardar el cliente. Por favor, inténtelo de nuevo.'
            );
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.clientes.form');
    }
}
