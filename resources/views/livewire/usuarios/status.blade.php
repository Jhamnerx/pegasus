<x-modal-card :title="$user?->activo ? 'Desactivar Usuario' : 'Activar Usuario'" wire:model="isOpen" max-width="md">

    @if ($user)
        <div class="space-y-4">
            <!-- Información del usuario -->
            <div class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <img class="h-12 w-12 rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                    @if ($user->rol)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rol: {{ $user->rol->nombre_rol }}</p>
                    @endif
                </div>
            </div>

            <!-- Estado actual -->
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <span class="text-sm font-medium text-gray-900 dark:text-white">Estado Actual:</span>
                <x-badge :label="$user->activo ? 'Activo' : 'Inactivo'" :color="$user->activo ? 'positive' : 'negative'" />
            </div>

            <!-- Mensaje de confirmación -->
            <div
                class="p-4 rounded-lg {{ $user->activo ? 'bg-red-50 border border-red-200 dark:bg-red-900/20' : 'bg-green-50 border border-green-200 dark:bg-green-900/20' }}">
                <div class="flex">
                    <div class="flex-shrink-0">
                        @if ($user->activo)
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p
                            class="text-sm {{ $user->activo ? 'text-red-800 dark:text-red-200' : 'text-green-800 dark:text-green-200' }}">
                            @if ($user->activo)
                                <strong>¿Estás seguro de desactivar este usuario?</strong><br>
                                El usuario no podrá acceder al sistema hasta que sea reactivado.
                            @else
                                <strong>¿Estás seguro de activar este usuario?</strong><br>
                                El usuario podrá acceder al sistema nuevamente.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-slot name="footer">
        <div class="flex justify-end gap-2">
            <x-button wire:click="closeModal" outline>
                Cancelar
            </x-button>
            <x-button wire:click="toggleStatus" :color="$user?->activo ? 'negative' : 'positive'" spinner="toggleStatus">
                {{ $user?->activo ? 'Desactivar' : 'Activar' }} Usuario
            </x-button>
        </div>
    </x-slot>
</x-modal-card>
