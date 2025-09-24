<div>
    <!-- Modal de Formulario -->
    <x-modal-card :title="$isEditing ? 'Editar Cliente' : 'Nuevo Cliente'" wire:model="isOpen" max-width="2xl">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Nombre del Cliente -->
            <div class="col-span-1 sm:col-span-2">
                <x-input label="Nombre del Cliente *" placeholder="Ingrese el nombre del cliente"
                    wire:model="nombre_cliente" />
            </div>

            <!-- RUC/DNI -->
            <x-input label="RUC/DNI *" placeholder="Ingrese RUC o DNI" wire:model="ruc_dni" />

            <!-- Teléfonos -->
            <div class="col-span-1 sm:col-span-2">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Teléfonos de Contacto</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input label="Teléfono Principal" placeholder="Teléfono principal" wire:model="telefono" />
                    <x-input label="Teléfono 1" placeholder="Teléfono adicional 1" wire:model="telefono_1" />
                    <x-input label="Teléfono 2" placeholder="Teléfono adicional 2" wire:model="telefono_2" />
                    <x-input label="Teléfono 3" placeholder="Teléfono adicional 3" wire:model="telefono_3" />
                </div>
            </div>

            <!-- Correo Electrónico -->
            <div class="col-span-1 sm:col-span-2">
                <x-input label="Correo Electrónico" placeholder="Ingrese el correo electrónico"
                    wire:model="correo_electronico" type="email" />
            </div>

            <!-- Dirección -->
            <div class="col-span-1 sm:col-span-2">
                <x-input label="Dirección" placeholder="Ingrese la dirección" wire:model="direccion" />
            </div>

            <!-- Estado -->
            <div class="col-span-1 sm:col-span-2">
                <x-select label="Estado del Cliente *" placeholder="Seleccione el estado" wire:model="estado">
                    <x-select.option label="Activo" value="Activo" />
                    <x-select.option label="Inactivo" value="Inactivo" />
                </x-select>
            </div>
        </div>

        <x-slot name="footer" class="flex justify-between gap-x-4">
            <div class="flex gap-x-4">
                <x-button flat label="Cancelar" wire:click="closeModal" />

                <x-button primary label="{{ $isEditing ? 'Actualizar' : 'Guardar' }}" wire:click="save" />
            </div>
        </x-slot>
    </x-modal-card>
</div>
