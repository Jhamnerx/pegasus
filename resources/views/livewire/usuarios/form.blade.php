<x-modal-card title="{{ $isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}" wire:model="isOpen" max-width="lg">
    <div class="space-y-4">
        <!-- Nombre -->
        <x-input wire:model="name" label="Nombre Completo" placeholder="Juan Pérez" required />

        <!-- Email -->
        <x-input wire:model="email" label="Correo Electrónico" type="email" placeholder="usuario@empresa.com" required />

        <!-- Username -->
        <x-input wire:model="username" label="Nombre de Usuario" placeholder="jperez" required
            hint="Solo letras, números, guiones y guiones bajos" />

        <!-- Rol -->
        <x-select wire:model="rol_id" label="Rol" placeholder="Selecciona un rol" required>
            @foreach ($roles as $rol)
                <x-select.option label="{{ $rol->nombre_rol }}" value="{{ $rol->id }}" />
            @endforeach
        </x-select>

        <!-- Estado -->
        <div class="flex items-center space-x-3">
            <x-toggle wire:model="activo" label="Usuario Activo" />
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ $activo ? 'El usuario puede acceder al sistema' : 'El usuario no puede acceder al sistema' }}
            </span>
        </div>

        <!-- Contraseña -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            @if ($isEditing)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Deja los campos de contraseña vacíos si no deseas cambiarla.
                </p>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-password wire:model="password" label="Contraseña" placeholder="••••••••" :required="!$isEditing" />

                <x-password wire:model="password_confirmation" label="Confirmar Contraseña" placeholder="••••••••"
                    :required="!$isEditing" />
            </div>

            @if (!$isEditing)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    La contraseña debe tener al menos 8 caracteres.
                </p>
            @endif
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-2">
            <x-button wire:click="closeModal" outline>
                Cancelar
            </x-button>
            <x-button wire:click="save" primary spinner="save">
                {{ $isEditing ? 'Actualizar' : 'Crear' }} Usuario
            </x-button>
        </div>
    </x-slot>
</x-modal-card>
