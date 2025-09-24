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

                <!-- Prueba de WhatsApp -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-medium text-gray-800 dark:text-gray-100">Prueba de WhatsApp</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Envía un mensaje de prueba para
                                verificar que la integración de WhatsApp funciona correctamente.</p>
                        </div>
                        <x-button wire:click="abrirModalWhatsapp" outline sm>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                                </svg>
                                <span>Probar WhatsApp</span>
                            </div>
                        </x-button>
                    </div>
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

    <!-- Modal para Prueba de WhatsApp -->
    <x-modal-card title="Probar Servicio de WhatsApp" wire:model="isOpenModalWhatsapp" width="md">
        <div class="space-y-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            Utilice esta función para verificar que el servicio de WhatsApp esté configurado
                            correctamente.
                            El mensaje se enviará al número especificado.
                        </p>
                    </div>
                </div>
            </div>

            <x-input wire:model="numeroWhatsapp" label="Número de WhatsApp" required
                placeholder="Ej: +51915274968 o 915274968"
                hint="Incluya el código de país o déjelo vacío para Perú (+51)" />

            <x-textarea wire:model="mensajeWhatsapp" label="Mensaje de Prueba" required rows="3"
                placeholder="Escribe el mensaje que deseas enviar como prueba..." />

            @error('numeroWhatsapp')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            @error('mensajeWhatsapp')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-button wire:click="cerrarModalWhatsapp" outline>
                    Cancelar
                </x-button>
                <x-button wire:click="enviarPruebaWhatsapp" primary>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                        </svg>
                        <span>Enviar Mensaje</span>
                    </div>
                </x-button>
            </div>
        </x-slot>
    </x-modal-card>

</div>
