<div>
    {{-- Modal de anulación --}}
    <x-modal-card title="Anular Recibo" wire:model="showModal" max-width="md">
        @if ($recibo)
            {{-- Recibo Details --}}
            <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Número:</span>
                        <span class="text-gray-900 dark:text-white">{{ $recibo->numero_recibo }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Cliente:</span>
                        <span
                            class="text-gray-900 dark:text-white">{{ $recibo->data_cliente['nombre_cliente'] ?? 'Sin cliente' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Monto:</span>
                        <span class="text-gray-900 dark:text-white">S/
                            {{ number_format($recibo->monto_recibo, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Estado:</span>
                        <span class="text-gray-900 dark:text-white">
                            @switch($recibo->estado_recibo)
                                @case('pendiente')
                                    <x-badge warning label="Pendiente" />
                                @break

                                @case('pagado')
                                    <x-badge positive label="Pagado" />
                                @break

                                @case('vencido')
                                    <x-badge negative label="Vencido" />
                                @break

                                @case('anulado')
                                    <x-badge secondary label="Anulado" />
                                @break

                                @default
                                    <x-badge secondary label="{{ ucfirst($recibo->estado_recibo) }}" />
                            @endswitch
                        </span>
                    </div>
                    @if ($recibo->fecha_vencimiento)
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Vencimiento:</span>
                            <span
                                class="text-gray-900 dark:text-white">{{ $recibo->fecha_vencimiento->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Warning Message --}}
            <div
                class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-700 rounded-md">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    {{ $warningMessage }}
                </p>
            </div>

            {{-- Motivo de Anulación --}}
            <div class="mb-6">
                <x-textarea label="Motivo de la Anulación *"
                    placeholder="Describe el motivo por el cual se anula este recibo..." wire:model="motivoAnulacion"
                    rows="3" />
            </div>
        @endif

        <x-slot name="footer">
            <div class="flex justify-between w-full">
                <x-button flat label="Cancelar" wire:click="closeModal" />
                <x-button negative label="{{ $buttonText }}" wire:click="anular" icon="trash" />
            </div>
        </x-slot>
    </x-modal-card>
</div>
