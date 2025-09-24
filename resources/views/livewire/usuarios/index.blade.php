<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Usuarios</h1>
            <p class="text-gray-600 dark:text-gray-400">Gestión de usuarios del sistema</p>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Search -->
            <x-input wire:model.live.debounce.300ms="search" placeholder="Buscar usuario..." class="w-64" />

            <!-- Add user button -->
            <x-button primary label="Nuevo Usuario" wire:click="openCreateForm" icon="plus" />
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead
                    class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3">Usuario</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Rol</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Fecha Registro</th>
                        <th class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="{{ $usuario->profile_photo_url }}"
                                            alt="{{ $usuario->name }}" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $usuario->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                {{ $usuario->email }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($usuario->rol)
                                    <x-badge :label="$usuario->rol->nombre_rol" color="info" />
                                @else
                                    <x-badge label="Sin rol" color="warning" />
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :label="$usuario->activo ? 'Activo' : 'Inactivo'" :color="$usuario->activo ? 'positive' : 'negative'" />
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                {{ $usuario->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <x-button xs warning icon="pencil" title="Editar usuario"
                                        wire:click="openEditForm({{ $usuario->id }})" />
                                    <x-button xs :color="$usuario->activo ? 'negative' : 'positive'" :icon="$usuario->activo ? 'x-circle' : 'check-circle'" :title="$usuario->activo ? 'Desactivar usuario' : 'Activar usuario'"
                                        wire:click="openStatusModal({{ $usuario->id }})" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                @if ($search)
                                    No se encontraron usuarios que coincidan con "{{ $search }}"
                                @else
                                    No hay usuarios registrados
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($usuarios->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <!-- Componentes Livewire -->
    @livewire('usuarios.form')
    @livewire('usuarios.status')

</div>
