<div>
    <!-- Modal para formulario de cobros -->
    <x-modal-card :title="$isEditing ? 'Editar Cobro' : 'Nuevo Cobro'" wire:model.live="isOpen" width="7xl">
        {{ json_encode($errors->all()) }}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Cliente -->
            <div class="lg:col-span-3">
                <x-select label="Cliente *" placeholder="Seleccione un cliente" wire:model.live="cliente_id"
                    :async-data="[
                        'api' => route('select.clientes'),
                        'method' => 'GET',
                    ]" option-label="label" option-value="value" option-description="description" />
            </div>

            <!-- Servicio -->
            <div class="lg:col-span-3">
                <x-select label="Servicio" placeholder="Seleccione un servicio" wire:model.live="servicio_id"
                    :async-data="[
                        'api' => route('select.servicios'),
                        'method' => 'GET',
                    ]" option-label="label" option-value="value" option-description="description" />
            </div>

            <!-- Descripción personalizada (si no hay servicio) -->
            <div class="lg:col-span-3">
                <x-input label="Descripción Personalizada" placeholder="Ingrese descripción del servicio personalizado"
                    wire:model="descripcion_servicio_personalizado" />
            </div>

            <!-- Monto Base -->
            <div>
                <x-currency label="Monto Base" placeholder="0.00" wire:model.live="monto_base" precision="2"
                    required />
            </div>

            <!-- Monto Total Calculado -->
            <div class="lg:col-span-2">
                <x-input label="Monto Total" value="{{ number_format($monto_total_calculado, 2) }}" readonly
                    prefix="S/" />
            </div>

            <!-- Período de Facturación -->
            <div>
                <x-select label="Período de Facturación *" placeholder="Seleccione período" :clearable="false"
                    wire:model.live="periodo_facturacion">
                    <x-select.option label="Mensual" value="Mensual" />
                    <x-select.option label="Bimensual" value="Bimensual" />
                    <x-select.option label="Trimestral" value="Trimestral" />
                    <x-select.option label="Semestral" value="Semestral" />
                    <x-select.option label="Anual" value="Anual" />
                </x-select>
            </div>

            <!-- Fecha Inicio Período -->
            <div>
                <x-datetime-picker wire:model.live="fecha_inicio_periodo" label="Fecha Inicio Período *"
                    placeholder="Fecha de inicio del período" parse-format="DD-MM-YYYY" display-format="DD/MM/YYYY"
                    without-time required :clearable="false" />
            </div>

            <!-- Fecha Fin Período -->
            <div>
                <x-datetime-picker wire:model.live="fecha_fin_periodo" label="Fecha Fin Período *"
                    placeholder="Fecha de fin del período" parse-format="DD-MM-YYYY" display-format="DD/MM/YYYY"
                    without-time required :clearable="false" />
            </div>

            <!-- Moneda -->
            <div>
                <x-select label="Moneda *" placeholder="Seleccione moneda" wire:model.live="moneda" :clearable="false">
                    <x-select.option label="Soles (PEN)" value="PEN" />
                    <x-select.option label="Dólares (USD)" value="USD" />
                </x-select>
            </div>

            <!-- Estado -->
            <div>
                <x-select label="Estado *" placeholder="Seleccione estado" wire:model.live="estado" :clearable="false">
                    <x-select.option label="Activo" value="activo" />
                    <x-select.option label="Procesado" value="procesado" />
                    <x-select.option label="Anulado" value="anulado" />
                </x-select>
            </div>

            <!-- Gestión de Placas -->
            <div class="lg:col-span-3">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 dark:text-gray-200">Gestión de Placas</h3>

                    <!-- Agregar nueva placa -->
                    <div class="flex gap-2 mb-4">
                        <div class="flex-1">
                            <x-input label="Nueva Placa" placeholder="Ej: ABC-123" wire:model.live="nueva_placa" />
                        </div>
                        <div class="flex items-end">
                            <x-button primary label="Agregar" wire:click="agregarPlaca" icon="plus" />
                        </div>
                    </div>
                    <div class="flex gap-2 mb-4">
                        @error('placas')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{ json_encode($placas) }}
                    <!-- Lista de placas -->
                    @if (count($placas) > 0)
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300">Placas agregadas:</h4>
                            <div class="space-y-4">
                                @foreach ($placas as $index => $placa)
                                    @if (is_array($placa) && isset($placa['placa']))
                                        <div
                                            class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-white dark:bg-gray-700">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $placa['placa'] }}</span>
                                                <x-button flat negative label="Remover"
                                                    wire:click="removerPlaca({{ $index }})" size="xs" />
                                            </div>

                                            <!-- Fechas específicas por placa -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <x-datetime-picker
                                                        wire:model.live="placas.{{ $index }}.fecha_inicio"
                                                        wire:change="actualizarFechasPlaca({{ $index }})"
                                                        label="Fecha Inicio" placeholder="Fecha de inicio"
                                                        parse-format="DD-MM-YYYY" display-format="DD/MM/YYYY"
                                                        without-time size="sm" />
                                                </div>
                                                <div>
                                                    <x-datetime-picker
                                                        wire:model.live="placas.{{ $index }}.fecha_fin"
                                                        wire:change="actualizarFechasPlaca({{ $index }})"
                                                        label="Fecha Fin" placeholder="Fecha de fin"
                                                        parse-format="DD-MM-YYYY" display-format="DD/MM/YYYY"
                                                        without-time size="sm" />
                                                </div>
                                            </div>

                                            <!-- Información de cálculo -->
                                            <div class="bg-gray-50 dark:bg-gray-600 rounded-lg p-3">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-300">Días
                                                            facturados:</span>
                                                        <span
                                                            class="font-medium text-gray-900 dark:text-gray-100">{{ $placa['dias_prorrateados'] ?? 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-300">Factor
                                                            prorrateo:</span>
                                                        <span
                                                            class="font-medium text-gray-900 dark:text-gray-100">{{ number_format(($placa['factor_prorateo'] ?? 1) * 100, 2) }}%</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600 dark:text-gray-300">Monto:</span>
                                                        <span class="font-bold text-green-600 dark:text-green-400">S/
                                                            {{ number_format($placa['monto_calculado'], 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-3 mt-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-blue-800 dark:text-blue-200 font-medium">Total de placas:
                                        {{ count($placas) }}</span>
                                    <span class="text-blue-800 dark:text-blue-200 font-bold">Total: S/
                                        {{ number_format($monto_total_calculado, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">añade placas específicas para calcular el
                            monto total.</p>
                    @endif
                </div>
            </div>

            <!-- Notas -->
            <div class="lg:col-span-3">
                <x-textarea label="Notas" placeholder="Observaciones adicionales..." wire:model="notas"
                    rows="3" />
            </div>
        </div>

        <x-slot name="footer" class="flex justify-between gap-x-4">
            @if ($isEditing)
                <x-button flat negative label="Eliminar"
                    wire:click="$dispatch('openDeleteModal', { cobroId: {{ $cobro?->id }} })" />
            @else
                <div></div>
            @endif

            <div class="flex gap-x-4">
                <x-button flat label="Cancelar" wire:click="closeModal" />

                <x-button primary label="{{ $isEditing ? 'Actualizar' : 'Guardar' }}" wire:click="save" />
            </div>
        </x-slot>
    </x-modal-card>
</div>
