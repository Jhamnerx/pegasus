<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Clientes</h1>
            <p class="text-gray-600 dark:text-gray-400">Gestión de clientes</p>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Search -->
            <x-input wire:model.live.debounce.300ms="search" placeholder="Buscar cliente..." class="w-64" />

            <!-- Add client button -->
            <x-button primary label="Nuevo Cliente" wire:click="openCreateForm" icon="plus" />
        </div>
    </div>

    <!-- Clients Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead
                    class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">RUC/DNI</th>
                        <th class="px-6 py-3">Contacto</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Fecha Registro</th>
                        <th class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $cliente->nombre_cliente }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                {{ $cliente->ruc_dni }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 dark:text-white">{{ $cliente->correo_electronico ?? 'N/A' }}
                                </div>
                                @if ($cliente->telefono)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $cliente->telefono }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :label="$cliente->estado" :color="$cliente->estado === 'Activo' ? 'positive' : 'warning'" />
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $cliente->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <x-button xs warning icon="pencil" title="Editar cliente"
                                        wire:click="openEditForm({{ $cliente->id }})" />
                                    <x-button xs negative icon="trash" title="Eliminar cliente"
                                        wire:click="openModalDelete({{ $cliente->id }})" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                @if ($search)
                                    No se encontraron clientes que coincidan con "{{ $search }}"
                                @else
                                    No hay clientes registrados
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($clientes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    <!-- Componentes Livewire -->
    @livewire('clientes.form')
    @livewire('clientes.delete')

</div>
