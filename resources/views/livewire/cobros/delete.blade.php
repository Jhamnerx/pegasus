<div>
    <!-- Modal de confirmación de eliminación -->
    <x-modal-card title="Eliminar Cobro" wire:model="isOpen" width="lg">
        @if ($cobro)
            <div class="space-y-4">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900 rounded-full">
                    <x-icon name="exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>

                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        ¿Estás seguro que deseas eliminar este cobro?
                    </p>

                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-left space-y-2">
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Cliente:</span>
                            <span
                                class="text-gray-900 dark:text-white">{{ $cobro->cliente->nombre_cliente ?? 'Cliente eliminado' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Servicio:</span>
                            <span
                                class="text-gray-900 dark:text-white">{{ $cobro->servicio?->nombre_servicio ?? ($cobro->descripcion_servicio_personalizado ?? 'No especificado') }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Período:</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ $cobro->fecha_inicio_periodo->format('d/m/Y') }} -
                                {{ $cobro->fecha_fin_periodo->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">Monto:</span>
                            <span class="text-gray-900 dark:text-white">
                                {{ $cobro->moneda }} {{ number_format($cobro->monto_total, 2) }}
                            </span>
                        </div>
                    </div>

                    <p class="text-sm text-red-600 dark:text-red-400 font-medium mt-4">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
        @endif

        <x-slot name="footer" class="flex justify-end gap-x-4">
            <x-button flat label="Cancelar" wire:click="closeModal" />
            <x-button negative label="Eliminar" wire:click="delete" />
        </x-slot>
    </x-modal-card>
</div>
