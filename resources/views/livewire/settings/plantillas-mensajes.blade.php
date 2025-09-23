<div class="grow">

    <!-- Panel body -->
    <div class="p-6 space-y-6">
        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Plantillas de Mensajes</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Lista de Plantillas -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Plantillas</h3>
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($plantillas as $plantilla)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer @if ($plantillaSeleccionada && $plantillaSeleccionada->id === $plantilla->id) bg-blue-50 dark:bg-blue-900/20 @endif"
                                wire:click="seleccionarPlantilla({{ $plantilla->id }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                                            {{ $plantilla->nombre }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ ucfirst(str_replace('_', ' ', $plantilla->tipo)) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if ($plantilla->activo)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Activo
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Inactivo
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <div class="text-gray-400 dark:text-gray-500 mb-2">
                                    <x-icon name="document-text" class="mx-auto h-12 w-12" />
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay plantillas configuradas</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Editor de Plantilla -->
            <div class="lg:col-span-2">
                @if ($editando)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                Editar Plantilla: {{ $plantillaSeleccionada->nombre }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Tipo: {{ ucfirst(str_replace('_', ' ', $plantillaSeleccionada->tipo)) }}
                            </p>
                        </div>

                        <div class="p-6 space-y-6">
                            <form wire:submit="guardarPlantilla" class="space-y-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Mensaje de la Plantilla
                                    </label>
                                    <x-textarea wire:model="mensaje" rows="12" required
                                        placeholder="Escriba el mensaje de la plantilla aquí..." />
                                </div>

                                <div
                                    class="flex items-center space-x-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <x-button wire:click="guardarPlantilla" primary type="submit">
                                        Actualizar Plantilla
                                    </x-button>
                                    <x-button wire:click="cancelarEdicion" secondary>
                                        Cancelar
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="p-8 text-center">
                            <div class="text-gray-400 dark:text-gray-500 mb-4">
                                <x-icon name="document-text" class="mx-auto h-16 w-16" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">
                                Gestión de Plantillas de Mensajes
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Selecciona una plantilla de la lista para editar su mensaje.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Variables Disponibles -->
                @if ($editando && $plantillaSeleccionada)
                    <div
                        class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <div class="p-4 border-b border-blue-200 dark:border-blue-700">
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200">
                                Variables Disponibles para
                                {{ ucfirst(str_replace('_', ' ', $plantillaSeleccionada->tipo)) }}
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach ($this->getVariablesDisponibles() as $variable => $descripcion)
                                    <div
                                        class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded border">
                                        <div class="flex-1">
                                            <code class="text-xs font-mono text-blue-600 dark:text-blue-400">
                                                {{ $variable }}
                                            </code>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $descripcion }}</p>
                                        </div>
                                        <x-button wire:click="insertarVariable('{{ $variable }}')" xs outline>
                                            Insertar
                                        </x-button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
