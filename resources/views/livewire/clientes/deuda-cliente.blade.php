<x-modal-card title="Resumen de Deudas" wire:model="isOpen" max-width="4xl">
    @if ($cliente)
        <!-- Información del Cliente -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $cliente->nombre_cliente }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        RUC/DNI: {{ $cliente->ruc_dni }}
                    </p>
                    @if ($cliente->telefono)
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            📱 {{ $cliente->telefono }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Adeudado</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                        S/ {{ number_format($totalDeuda, 2) }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ count($recibos) }} recibo(s) pendiente(s)
                    </p>
                </div>
            </div>
        </div>

        <!-- Lista de Recibos No Pagados -->
        @if (count($recibos) > 0)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Recibos Pendientes de Pago
                </h4>
                <div class="max-h-64 overflow-y-auto border dark:border-gray-600 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Recibo
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Servicio
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Vencimiento
                                </th>
                                <th
                                    class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Monto
                                </th>
                                <th
                                    class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($recibos as $recibo)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                                        {{ str_pad($recibo['numero_recibo'], 8, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $recibo['data_servicio']['nombre_servicio'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($recibo['fecha_vencimiento'])->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                        S/ {{ number_format($recibo['monto_recibo'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $vencido = \Carbon\Carbon::parse($recibo['fecha_vencimiento'])->isPast();
                                        @endphp
                                        <x-badge :label="$vencido ? 'Vencido' : 'Pendiente'" :color="$vencido ? 'negative' : 'warning'" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mensaje de WhatsApp -->
            <div class="mb-4">
                <x-textarea wire:model="mensajePersonalizado" label="Mensaje de Recordatorio"
                    placeholder="Escribe el mensaje que se enviará por WhatsApp..." rows="8" />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    💡 Este mensaje será enviado al número de teléfono del cliente:
                    <span class="font-semibold">{{ $cliente->telefono_principal ?? 'No disponible' }}</span>
                </p>
            </div>
        @else
            <div class="py-8 text-center">
                <div class="text-green-600 dark:text-green-400 mb-2">
                    <x-icon name="check-circle" class="w-16 h-16 mx-auto" />
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    ¡Este cliente no tiene deudas pendientes!
                </p>
            </div>
        @endif
    @endif

    <x-slot name="footer">
        <div class="flex justify-between items-center w-full">
            <x-button flat label="Cerrar" wire:click="closeModal" />

            @if (count($recibos) > 0 && $cliente && $cliente->tieneTelefono())
                <x-button primary label="Enviar Recordatorio por WhatsApp" icon="chat-bubble-left-right"
                    wire:click="enviarRecordatorio" spinner="enviarRecordatorio" />
            @elseif(count($recibos) > 0 && (!$cliente || !$cliente->tieneTelefono()))
                <x-badge negative label="Cliente sin teléfono registrado" />
            @endif
        </div>
    </x-slot>
</x-modal-card>
