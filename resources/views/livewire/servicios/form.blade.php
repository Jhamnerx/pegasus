<x-modal-card title="{{ $isEditing ? 'Editar Servicio' : 'Crear Servicio' }}" wire:model="isOpen" max-width="md">
    <form wire:submit="save">
        <div class="space-y-4">
            <x-input label="Nombre del Servicio" placeholder="Ingrese el nombre del servicio" wire:model="nombre_servicio"
                required />

            <x-textarea label="Descripción" placeholder="Descripción del servicio (opcional)" wire:model="descripcion"
                rows="3" />

            <x-currency label="Precio Base" placeholder="0.00" wire:model="precio_base" precision="2" required />

            <x-select label="Estado" placeholder="Seleccione el estado" :options="[['value' => true, 'label' => 'Activo'], ['value' => false, 'label' => 'Inactivo']]" option-value="value"
                option-label="label" wire:model="activo" required />
        </div>
    </form>

    <x-slot name="footer" class="flex justify-end gap-x-2">
        <x-button secondary wire:click="closeModal" label="Cancelar" />
        <x-button primary wire:click="save" label="{{ $isEditing ? 'Actualizar' : 'Crear' }}"
            wire:loading.attr="disabled" />
    </x-slot>
</x-modal-card>
