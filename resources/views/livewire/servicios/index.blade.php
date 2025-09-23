<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Servicios</h1>
            <p class="text-gray-600 dark:text-gray-400">Gestión de servicios del sistema GPS</p>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Search -->
            <x-input wire:model.live.debounce.300ms="search" placeholder="Buscar servicio..." class="w-64" />

            <!-- Add service button -->
            <x-button primary label="Nuevo Servicio" wire:click="$dispatch('openCreateForm')" icon="plus" />
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-5 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Estado Filter -->
                <div>
                    <x-select label="Estado" wire:model.live="estadoFilter" :options="[
                        ['label' => 'Todos', 'value' => 'all'],
                        ['label' => 'Activos', 'value' => 'activo'],
                        ['label' => 'Inactivos', 'value' => 'inactivo'],
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
                        option-value="value" />
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <x-button secondary label="Limpiar Filtros" wire:click="resetFilters" icon="arrow-path" />
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                Servicios
                <span class="text-gray-400 dark:text-gray-500 font-medium">
                    ({{ $servicios->total() }})
                </span>
            </h2>
        </header>

        <div class="p-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3">Servicio</th>
                            <th class="px-6 py-3">Descripción</th>
                            <th class="px-6 py-3">Precio Base</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($servicios as $servicio)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $servicio->nombre_servicio }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900 dark:text-white max-w-xs truncate">
                                        {{ $servicio->descripcion ?? 'Sin descripción' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        S/ {{ number_format($servicio->precio_base, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :label="$servicio->activo ? 'Activo' : 'Inactivo'" :color="$servicio->activo ? 'positive' : 'negative'" />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <x-button xs warning icon="pencil" title="Editar servicio"
                                            wire:click="$dispatch('openEditForm', { servicio: {{ $servicio->id }} })" />
                                        <x-button xs negative icon="trash" title="Eliminar servicio"
                                            wire:click="$dispatch('openDeleteModal', { servicioId: {{ $servicio->id }} })" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    @if ($search)
                                        No se encontraron servicios que coincidan con "{{ $search }}"
                                    @else
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <x-icon name="cog" class="w-12 h-12 text-gray-300 mb-4" />
                                            <p class="text-lg font-medium">No hay servicios registrados</p>
                                            <p class="text-sm">Comienza creando tu primer servicio</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($servicios->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $servicios->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Componentes Livewire -->
    @livewire('servicios.form')
    @livewire('servicios.delete')

</div>
