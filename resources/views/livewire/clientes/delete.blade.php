<div>
    {{-- Modal de Confirmación de Eliminación --}}
    <x-modal-card title="Eliminar Cliente" wire:model="showModal" max-width="md">

        @if ($cliente)
            <div class="text-center">
                {{-- Ícono de advertencia --}}
                <div
                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 mb-4">
                    <x-icon name="exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>

                {{-- Mensaje de confirmación --}}
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    ¿Está seguro que desea eliminar este cliente?
                </h3>

                {{-- Información del cliente --}}
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                    <div class="text-sm space-y-1">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $cliente->nombre_cliente }}
                        </div>
                        <div class="text-gray-600 dark:text-gray-400">
                            RUC/DNI: {{ $cliente->ruc_dni }}
                        </div>
                        @if ($cliente->correo_electronico)
                            <div class="text-gray-600 dark:text-gray-400">
                                Email: {{ $cliente->correo_electronico }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Advertencia --}}
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Esta acción no se puede deshacer. El cliente será eliminado permanentemente del sistema.
                </p>
            </div>
        @endif

        <x-slot name="footer" class="flex justify-end gap-x-4">
            <x-button flat label="Cancelar" wire:click="closeModal" />
            <x-button negative label="Sí, eliminar" wire:click="confirmDelete" />
        </x-slot>
    </x-modal-card>
</div>
