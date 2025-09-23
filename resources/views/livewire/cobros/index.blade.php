<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Cobros</h1>
            <p class="text-gray-600 dark:text-gray-400">Gestión de cobros y facturación del sistema GPS</p>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Search -->
            <x-input wire:model.live.debounce.300ms="search" placeholder="Buscar cobro..." class="w-64" />

            <!-- Add cobro button -->
            <x-button primary label="Nuevo Cobro" wire:click="$dispatch('openCreateForm')" icon="plus" />
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-5 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Estado Filter -->
                <div>
                    <x-select label="Estado" wire:model.live="estadoFilter" :options="[
                        ['label' => 'Todos', 'value' => 'all'],
                        ['label' => 'Activo', 'value' => 'activo'],
                        ['label' => 'Procesado', 'value' => 'procesado'],
                        ['label' => 'Anulado', 'value' => 'anulado'],
                    ]" option-label="label"
                        option-value="value" :clearable="false" />
                </div>

                <!-- Período Filter -->
                <div>
                    <x-select label="Período" wire:model.live="periodoFilter" :options="[
                        ['label' => 'Todos', 'value' => ''],
                        ['label' => 'Mes actual', 'value' => 'actual'],
                        ['label' => 'Mes anterior', 'value' => 'anterior'],
                    ]" option-label="label"
                        option-value="value" />
                </div>

                <!-- Per Page -->
                <div>
                    <x-select label="Por página" wire:model.live="perPage" :options="[
                        ['label' => '5', 'value' => 5],
                        ['label' => '10', 'value' => 10],
                        ['label' => '25', 'value' => 25],
                        ['label' => '50', 'value' => 50],
                    ]" option-label="label"
                        :clearable="false" option-value="value" />
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <x-button secondary label="Limpiar Filtros" wire:click="resetFilters" icon="arrow-path" />
                </div>
            </div>
        </div>
    </div>

    <!-- Cobros Table -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                Cobros
                <span class="text-gray-400 dark:text-gray-500 font-medium">
                    ({{ $cobros->total() }})
                </span>
            </h2>
        </header>

        <div class="p-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3">Cliente</th>
                            <th class="px-6 py-3">Servicio</th>
                            <th class="px-6 py-3">Período</th>
                            <th class="px-6 py-3">Placas</th>
                            <th class="px-6 py-3">Monto</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($cobros as $cobro)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $cobro->cliente->nombre_cliente ?? 'Cliente eliminado' }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $cobro->cliente->ruc_dni ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 dark:text-white">
                                        {{ $cobro->servicio?->nombre_servicio ?? ($cobro->descripcion_servicio_personalizado ?? 'No especificado') }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $cobro->periodo_facturacion }} - {{ $cobro->cantidad_placas }} placas
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 dark:text-white">
                                        {{ $cobro->fecha_inicio_periodo->format('d/m/Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        al {{ $cobro->fecha_fin_periodo->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 dark:text-white font-medium">
                                        {{ $cobro->cantidad_placas }} placas
                                    </div>
                                    @if ($cobro->cobroPlacas && $cobro->cobroPlacas->count() > 0)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Específicas: {{ $cobro->cobroPlacas->pluck('placa')->join(', ') }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Cantidad genérica
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $cobro->moneda }} {{ number_format($cobro->monto_total, 2) }}
                                    </div>
                                    @if ($cobro->monto_unitario != $cobro->monto_base)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Unitario: {{ $cobro->moneda }}
                                            {{ number_format($cobro->monto_unitario, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($cobro->estado === 'activo')
                                        <x-badge positive label="Activo" />
                                    @elseif($cobro->estado === 'procesado')
                                        <x-badge warning label="Procesado" />
                                    @else
                                        <x-badge negative label="Anulado" />
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <x-button xs secondary icon="eye" title="Ver cobro"
                                            wire:click="$dispatch('openShowModal', { cobro: {{ $cobro->id }} })" />
                                        <x-button xs warning icon="pencil" title="Editar cobro"
                                            wire:click="$dispatch('openEditForm', { cobro: {{ $cobro->id }} })" />
                                        <x-button xs negative icon="trash" title="Eliminar cobro"
                                            wire:click="$dispatch('openDeleteModal', { cobroId: {{ $cobro->id }} })" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    @if ($search)
                                        No se encontraron cobros que coincidan con "{{ $search }}"
                                    @else
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <x-icon name="banknotes" class="w-12 h-12 text-gray-300 mb-4" />
                                            <p class="text-lg font-medium">No hay cobros registrados</p>
                                            <p class="text-sm">Comienza creando tu primer cobro</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($cobros->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $cobros->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Componentes Livewire -->
    @livewire('cobros.form')
    @livewire('cobros.delete')
    @livewire('cobros.show')

</div>
