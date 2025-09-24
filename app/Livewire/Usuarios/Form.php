<?php

namespace App\Livewire\Usuarios;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Form extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?int $userId = null;

    public bool $isEditing = false;

    // Campos del formulario
    public string $name = '';

    public string $email = '';

    public string $username = '';

    public ?int $rol_id = null;

    public bool $activo = true;

    public string $password = '';

    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users')->ignore($this->userId),
            ],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['boolean'],
            'password' => $this->isEditing
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    #[On('open-form-modal')]
    public function openModal(?int $userId = null): void
    {
        $this->reset();
        $this->userId = $userId;
        $this->isEditing = ! is_null($userId);

        if ($this->isEditing) {
            $this->loadUser();
        } else {
            $this->activo = true;
        }

        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->isOpen = false;
    }

    private function loadUser(): void
    {
        $user = User::findOrFail($this->userId);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username ?? '';
        $this->rol_id = $user->rol_id;
        $this->activo = $user->activo;
        // No cargamos la contraseña por seguridad
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'username' => $this->username,
                'rol_id' => $this->rol_id,
                'activo' => $this->activo,
            ];

            // Solo agregar contraseña si se proporcionó
            if (! empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->isEditing) {
                User::findOrFail($this->userId)->update($data);
                $this->notification()->success('Usuario actualizado correctamente');
            } else {
                // Para nuevos usuarios, la contraseña es obligatoria
                $data['password'] = Hash::make($this->password);
                User::create($data);
                $this->notification()->success('Usuario creado correctamente');
            }

            $this->dispatch('user-saved');
            $this->closeModal();
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'Ocurrió un error al guardar el usuario: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('livewire.usuarios.form', compact('roles'));
    }
}
