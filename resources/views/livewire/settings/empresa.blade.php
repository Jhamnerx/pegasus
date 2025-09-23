<div class="grow">

    <!-- Panel body -->
    <div class="p-6 space-y-6">
        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">My Account</h2>
        <!-- Panel -->

        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Configuraciones de Empresa</h2>

        @if (session()->has('message'))
            <div class="mb-6 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('message') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Información General -->
        <section>
            <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Información
                General</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-5">Administra la información básica de
                tu empresa.</div>

            <form wire:submit="actualizarConfiguraciones" class="space-y-6">
                <div class="space-y-4">
                    <flux:input wire:model="nombre" label="Nombre de la Empresa" type="text" required
                        placeholder="PEGASUS S.A.C." />

                    <flux:textarea wire:model="direccion" label="Dirección" required rows="3"
                        placeholder="Dirección completa de la empresa" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="telefono" label="Teléfono" type="tel" required
                            placeholder="+51 915274968" />

                        <flux:input wire:model="correo" label="Correo Electrónico" type="email" required
                            placeholder="contacto@empresa.com" />
                    </div>

                    <flux:select wire:model="moneda" label="Moneda por Defecto" required>
                        <option value="PEN">PEN - Sol Peruano</option>
                        <option value="USD">USD - Dólar Americano</option>
                        <option value="EUR">EUR - Euro</option>
                    </flux:select>
                </div>

                <!-- Logo de la Empresa -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <h4 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-4">Logo de la Empresa
                    </h4>

                    @if ($logoUrl)
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex-shrink-0">
                                <img src="{{ $logoUrl }}" alt="Logo actual"
                                    class="h-16 w-16 rounded-lg object-contain border border-gray-200 dark:border-gray-700 bg-white">
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Logo actual de la
                                    empresa</p>
                                <div class="mt-2">
                                    <flux:button wire:click="eliminarLogo" variant="outline" size="sm"
                                        type="button">
                                        Eliminar Logo
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subir
                            Nuevo Logo</label>
                        <input type="file" wire:model="logo" accept="image/*"
                            class="block w-full text-sm text-gray-500 dark:text-gray-400
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-lg file:border-0
                                           file:text-sm file:font-medium
                                           file:bg-blue-50 file:text-blue-700
                                           hover:file:bg-blue-100
                                           dark:file:bg-blue-900 dark:file:text-blue-200
                                           dark:hover:file:bg-blue-800" />
                        @if ($logo)
                            <div class="flex items-center space-x-2 mt-2">
                                <p class="text-sm text-green-600 dark:text-green-400">Archivo seleccionado:
                                    {{ $logo->getClientOriginalName() }}</p>
                                <flux:button wire:click="limpiarLogo" variant="outline" size="sm" type="button">
                                    Cancelar
                                </flux:button>
                            </div>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Formatos soportados: JPG, PNG, GIF. Tamaño máximo: 2MB
                        </p>
                    </div>

                    @error('logo')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Métodos de Pago -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-medium text-gray-800 dark:text-gray-100">Métodos de Pago</h4>
                        <flux:button wire:click="abrirModalMetodo" variant="primary" size="sm" type="button">
                            Agregar Método
                        </flux:button>
                    </div>

                    @if (count($metodosPago) > 0)
                        <div class="space-y-2">
                            @foreach ($metodosPago as $index => $metodo)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span
                                        class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $metodo }}</span>
                                    <div class="flex items-center space-x-2">
                                        <flux:button wire:click="editarMetodoPago({{ $index }})"
                                            variant="outline" size="xs" type="button">
                                            Editar
                                        </flux:button>
                                        <flux:button wire:click="eliminarMetodoPago({{ $index }})"
                                            variant="danger" size="xs" type="button"
                                            wire:confirm="¿Estás seguro de eliminar este método de pago?">
                                            Eliminar
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-gray-400 dark:text-gray-500 mb-2">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No hay métodos de pago configurados</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Agrega tu primer método de pago</p>
                        </div>
                    @endif
                </div>

                <!-- Botón de Guardar -->
                <div class="flex items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="primary" type="submit">
                        Guardar Configuraciones
                    </flux:button>

                    <x-action-message class="me-3" on="empresa-updated">
                        Guardado.
                    </x-action-message>
                </div>
            </form>
        </section>

    </div>

    <!-- Modal para Métodos de Pago -->
    <x-modal-card title="{{ $metodoPagoEditandoIndex !== null ? 'Editar Método de Pago' : 'Agregar Método de Pago' }}"
        wire:model="isOpenModalMetodo" width="md">
        <div class="space-y-4">
            <flux:input wire:model="nuevoMetodoPago" label="Nombre del Método de Pago" type="text" required
                placeholder="Ej: Efectivo, Tarjeta de crédito, Transferencia bancaria" />

            @error('nuevoMetodoPago')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <flux:button wire:click="cerrarModalMetodo" variant="outline" type="button">
                    Cancelar
                </flux:button>
                <flux:button wire:click="guardarMetodoPago" variant="primary" type="button">
                    {{ $metodoPagoEditandoIndex !== null ? 'Actualizar' : 'Agregar' }}
                </flux:button>
            </div>
        </x-slot>
    </x-modal-card>

</div>
