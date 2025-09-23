<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Recibos</h1>
            <p class="text-gray-600 dark:text-gray-400">Gestión de recibos del sistema GPS</p>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Search -->
            <x-input wire:model.live.debounce.300ms="search" placeholder="Buscar recibo..." class="w-64" />

            <!-- Add recibo button -->
            {{-- <x-button primary label="Nuevo Recibo" wire:click="openCreateForm" icon="plus" /> --}}
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-5 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Estado Filter -->
                <div>
                    <x-select label="Estado" wire:model.live="estadoFilter" :options="collect($estadosDisponibles)->map(
                        fn($label, $value) => ['label' => $label, 'value' => $value],
                    )" option-label="label"
                        option-value="value" />
                </div>

                <!-- Per Page -->
                <div>
                    <x-select label="Por página" wire:model.live="perPage" :options="[
                        ['label' => '10', 'value' => 10],
                        ['label' => '25', 'value' => 25],
                        ['label' => '50', 'value' => 50],
                        ['label' => '100', 'value' => 100],
                    ]" option-label="label"
                        option-value="value" />
                </div>

                <!-- Actions -->
                <div class="md:col-span-2 flex items-end space-x-2">
                    <x-button secondary label="Limpiar Filtros" wire:click="resetFilters" icon="arrow-path" />
                    <x-button positive label="Exportar CSV" wire:click="exportar" icon="arrow-down-tray" />
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                Recibos
                <span class="text-gray-400 dark:text-gray-500 font-medium">
                    ({{ $recibos->total() }})
                </span>
            </h2>
        </header>

        <div class="p-3">
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <!-- Table header -->
                    <thead
                        class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700 dark:bg-opacity-50 rounded-sm">
                        <tr>
                            <th class="p-2 whitespace-nowrap">
                                <button wire:click="sortBy('numero_recibo')"
                                    class="font-semibold text-left flex items-center space-x-1 hover:text-gray-600">
                                    <span>Número</span>
                                    @if ($sortField === 'numero_recibo')
                                        @if ($sortDirection === 'asc')
                                            <x-icon name="chevron-up" class="w-4 h-4" />
                                        @else
                                            <x-icon name="chevron-down" class="w-4 h-4" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th class="p-2 whitespace-nowrap">
                                <button wire:click="sortBy('cliente_nombre')"
                                    class="font-semibold text-left flex items-center space-x-1 hover:text-gray-600">
                                    <span>Cliente</span>
                                    @if ($sortField === 'cliente_nombre')
                                        @if ($sortDirection === 'asc')
                                            <x-icon name="chevron-up" class="w-4 h-4" />
                                        @else
                                            <x-icon name="chevron-down" class="w-4 h-4" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th class="p-2 whitespace-nowrap">
                                <button wire:click="sortBy('monto_recibo')"
                                    class="font-semibold text-left flex items-center space-x-1 hover:text-gray-600">
                                    <span>Monto</span>
                                    @if ($sortField === 'monto_recibo')
                                        @if ($sortDirection === 'asc')
                                            <x-icon name="chevron-up" class="w-4 h-4" />
                                        @else
                                            <x-icon name="chevron-down" class="w-4 h-4" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th class="p-2 whitespace-nowrap">
                                <button wire:click="sortBy('fecha_vencimiento')"
                                    class="font-semibold text-left flex items-center space-x-1 hover:text-gray-600">
                                    <span>Vencimiento</span>
                                    @if ($sortField === 'fecha_vencimiento')
                                        @if ($sortDirection === 'asc')
                                            <x-icon name="chevron-up" class="w-4 h-4" />
                                        @else
                                            <x-icon name="chevron-down" class="w-4 h-4" />
                                        @endif
                                    @endif
                                </button>
                            </th>
                            <th class="p-2 whitespace-nowrap">
                                <div class="font-semibold text-center">Estado</div>
                            </th>
                            <th class="p-2 whitespace-nowrap">
                                <div class="font-semibold text-center">Acciones</div>
                            </th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($recibos as $recibo)
                            <tr wire:key="recibo-{{ $recibo->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <!-- Número -->
                                <td class="p-2 whitespace-nowrap">
                                    <div class="text-left font-medium text-gray-800 dark:text-gray-100">
                                        {{ $recibo->numero_recibo }}
                                    </div>
                                </td>
                                <!-- Cliente -->
                                <td class="p-2 whitespace-nowrap">
                                    <div class="text-left">
                                        <div class="font-medium text-gray-800 dark:text-gray-100">
                                            {{ $recibo->data_cliente['nombre_cliente'] ?? 'N/A' }}
                                        </div>
                                        <div class="text-gray-500 text-xs">
                                            {{ $recibo->data_cliente['ruc_dni'] ?? '' }}
                                        </div>
                                    </div>
                                </td>
                                <!-- Monto -->
                                <td class="p-2 whitespace-nowrap">
                                    <div class="text-left font-medium text-gray-800 dark:text-gray-100">
                                        {{ $recibo->cobro->moneda . ' ' . number_format($recibo->monto_recibo, 2) }}
                                    </div>
                                </td>
                                <!-- Vencimiento -->
                                <td class="p-2 whitespace-nowrap">
                                    <div class="text-left text-gray-800 dark:text-gray-100">
                                        @if ($recibo->fecha_vencimiento)
                                            {{ $recibo->fecha_vencimiento->format('d/m/Y') }}
                                            @if ($recibo->fecha_vencimiento < now() && $recibo->estado_recibo !== 'pagado')
                                                <span class="text-red-500 text-xs ml-1">(Vencido)</span>
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </td>
                                <!-- Estado -->
                                <td class="p-2 whitespace-nowrap text-center">
                                    @switch($recibo->estado_recibo)
                                        @case('pendiente')
                                            <x-badge warning label="Pendiente" />
                                        @break

                                        @case('pagado')
                                            <x-badge positive label="Pagado" />
                                        @break

                                        @case('anulado')
                                            <x-badge negative label="Anulado" />
                                        @break

                                        @default
                                            <x-badge secondary label="{{ ucfirst($recibo->estado_recibo) }}" />
                                    @endswitch
                                </td>
                                <!-- Acciones -->
                                <td class="p-2 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <x-mini-button rounded secondary icon="eye"
                                            wire:click="verDetalle({{ $recibo->id }})"
                                            title="Ver detalle del recibo" />
                                        <a href="{{ route('recibos.pdf', $recibo) }}" target="_blank" title="Ver PDF">
                                            <x-mini-button rounded info icon="document" />
                                        </a>
                                        @if ($recibo->estado_recibo === 'pendiente')
                                            <x-mini-button rounded positive icon="check-circle"
                                                wire:click="abrirModalPago({{ $recibo->id }})"
                                                title="Marcar como pagado" />
                                        @endif
                                        {{-- <x-mini-button rounded primary icon="pencil"
                                            wire:click="editRecibo({{ $recibo->id }})" /> --}}
                                        @if ($recibo->estado_recibo !== 'anulado')
                                            <x-mini-button rounded negative icon="trash"
                                                wire:click="confirmDelete({{ $recibo->id }})" />
                                        @else
                                            <span class="text-gray-400 cursor-not-allowed" title="Recibo anulado">
                                                <x-icon name="trash" class="w-5 h-5" />
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($recibos->count() < 1)
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <x-icon name="document-text" class="w-12 h-12 text-gray-300 mb-4" />
                                        <p class="text-lg font-medium">No hay recibos</p>
                                        <p class="text-sm">Comienza creando tu primer recibo</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            </div>

            <!-- Pagination -->
            @if ($recibos->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $recibos->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Componentes modales --}}
    @livewire('recibos.form')
    @livewire('recibos.delete')

    <x-modal-card title="Detalle del Recibo" wire:model="isOpenDetalle" width="7xl">
        @if ($selectedRecibo)
            <div class="space-y-6">
                <!-- Información del Cliente -->
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Información del Cliente
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($selectedRecibo->data_cliente)
                            @foreach ($selectedRecibo->data_cliente as $key => $value)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}:</p>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                        {{ $value ?? 'No especificado' }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Información del Servicio -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Información del Servicio
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($selectedRecibo->data_servicio)
                            @foreach ($selectedRecibo->data_servicio as $key => $value)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}:</p>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                        {{ is_array($value) ? json_encode($value) : $value ?? 'No especificado' }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Información de Cobro -->
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Información de Cobro</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($selectedRecibo->data_cobro)
                            @foreach ($selectedRecibo->data_cobro as $key => $value)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}:</p>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                        @if ($key === 'placas_incluidas' && is_array($value))
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach ($value as $placa)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {{ $placa }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @elseif (in_array($key, ['fecha_inicio_periodo', 'fecha_fin_periodo']) && $value)
                                            {{ \Carbon\Carbon::parse($value)->format('d/m/Y') }}
                                        @elseif ($key === 'monto_base' && is_numeric($value))
                                            {{ $selectedRecibo->cobro->moneda . ' ' . number_format($value, 2) }}
                                        @else
                                            {{ is_array($value) ? json_encode($value) : $value ?? 'No especificado' }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-button flat label="Cerrar" wire:click="$set('isOpenDetalle', false)" />
                </div>
            </x-slot>
        @endif
    </x-modal-card>

    <!-- Modal de pago -->
    <x-modal-card title="Marcar Recibo como Pagado" wire:model="isOpenPago" width="2xl">
        @if ($selectedRecibo)
            <div class="space-y-4">
                <!-- Información del recibo -->
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <h4 class="font-semibold mb-2">Información del Recibo</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Número:</span> {{ $selectedRecibo->numero_recibo }}
                        </div>
                        <div>
                            <span class="font-medium">Monto:</span> {{ $selectedRecibo->moneda }}
                            {{ number_format($selectedRecibo->monto_recibo, 2) }}
                        </div>
                        <div>
                            <span class="font-medium">Cliente:</span>
                            {{ $selectedRecibo->data_cliente['nombre_cliente'] ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="font-medium">Vencimiento:</span>
                            {{ $selectedRecibo->fecha_vencimiento?->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                <!-- Formulario de pago -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select label="Método de Pago *" placeholder="Seleccione un método"
                        wire:model="formPago.metodo_pago" :async-data="[
                            'api' => route('select.metodos-pago'),
                            'method' => 'GET',
                        ]" option-label="label"
                        option-value="value" />

                    <x-input label="Número de Referencia" wire:model="formPago.numero_referencia"
                        placeholder="Ej: 00123456789" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Monto Pagado *" type="number" step="0.01"
                        wire:model="formPago.monto_pagado" />

                    <x-input label="Fecha de Pago *" type="date" wire:model="formPago.fecha_pago" />
                </div>

                <x-textarea label="Observaciones" wire:model="formPago.observaciones"
                    placeholder="Observaciones adicionales del pago..." rows="3" />
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-button secondary label="Cancelar" wire:click="$set('isOpenPago', false)" />
                    <x-button positive label="Marcar como Pagado" wire:click="marcarComoPagado" />
                </div>
            </x-slot>
        @endif
    </x-modal-card>

</div>
