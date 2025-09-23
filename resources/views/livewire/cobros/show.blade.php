<div>
    <!-- Modal para mostrar detalles del cobro -->
    <x-modal-card title="Detalles del Cobro" wire:model="isOpen" width="7xl">
        @if ($cobro)
            <div class="space-y-6">
                <!-- Información del Cliente y Servicio -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            <x-icon name="user" class="w-5 h-5 inline mr-2" />
                            Información del Cliente
                        </h3>
                        <div class="space-y-2">
                            <div>
                                <span class="font-medium text-gray-600 dark:text-gray-300">Nombre:</span>
                                <span
                                    class="text-gray-900 dark:text-white">{{ $cobro->cliente->nombre_cliente ?? 'Cliente eliminado' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600 dark:text-gray-300">RUC/DNI:</span>
                                <span
                                    class="text-gray-900 dark:text-white">{{ $cobro->cliente->ruc_dni ?? 'N/A' }}</span>
                            </div>
                            @if ($cobro->cliente->direccion)
                                <div>
                                    <span class="font-medium text-gray-600 dark:text-gray-300">Dirección:</span>
                                    <span class="text-gray-900 dark:text-white">{{ $cobro->cliente->direccion }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            <x-icon name="cog-6-tooth" class="w-5 h-5 inline mr-2" />
                            Información del Servicio
                        </h3>
                        <div class="space-y-2">
                            <div>
                                <span class="font-medium text-gray-600 dark:text-gray-300">Servicio:</span>
                                <span class="text-gray-900 dark:text-white">
                                    {{ $cobro->servicio?->nombre_servicio ?? ($cobro->descripcion_servicio_personalizado ?? 'No especificado') }}
                                </span>
                            </div>
                            @if ($cobro->servicio?->descripcion)
                                <div>
                                    <span class="font-medium text-gray-600 dark:text-gray-300">Descripción:</span>
                                    <span
                                        class="text-gray-900 dark:text-white">{{ $cobro->servicio->descripcion }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="font-medium text-gray-600 dark:text-gray-300">Período:</span>
                                <span class="text-gray-900 dark:text-white">{{ $cobro->periodo_facturacion }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Financiera -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        <x-icon name="banknotes" class="w-5 h-5 inline mr-2" />
                        Información Financiera
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Monto Base:</span>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $cobro->moneda }} {{ number_format($cobro->monto_base, 2) }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Monto Total:</span>
                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ $cobro->moneda }} {{ number_format($cobro->monto_total, 2) }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Cantidad Placas:</span>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $cobro->cantidad_placas }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Estado:</span>
                            <div class="mt-1">
                                @if ($cobro->estado === 'activo')
                                    <x-badge warning label="Activo" />
                                @elseif($cobro->estado === 'procesado')
                                    <x-badge positive label="Procesado" />
                                @else
                                    <x-badge negative label="Anulado" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Fechas -->
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        <x-icon name="calendar-days" class="w-5 h-5 inline mr-2" />
                        Fechas y Períodos
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Fecha Inicio:</span>
                            <div class="text-gray-900 dark:text-white">
                                {{ $cobro->fecha_inicio_periodo->format('d/m/Y') }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Fecha Fin:</span>
                            <div class="text-gray-900 dark:text-white">
                                {{ $cobro->fecha_fin_periodo->format('d/m/Y') }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Días para Vencimiento:</span>
                            <div class="text-gray-900 dark:text-white">
                                {{ $cobro->dias_para_vencimiento }} días
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placas Asociadas -->
                @if ($cobro->cobroPlacas && $cobro->cobroPlacas->count() > 0)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            <x-icon name="rectangle-group" class="w-5 h-5 inline mr-2" />
                            Placas Detalladas ({{ $cobro->cobroPlacas->count() }})
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($cobro->cobroPlacas as $placaDetalle)
                                <div
                                    class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-800">
                                    <div class="font-bold text-lg text-gray-900 dark:text-white mb-2">
                                        {{ $placaDetalle->placa }}
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-300">Período:</span>
                                            <span class="text-gray-900 dark:text-white">
                                                {{ $placaDetalle->fecha_inicio->format('d/m/Y') }} -
                                                {{ $placaDetalle->fecha_fin->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-300">Días:</span>
                                            <span
                                                class="text-gray-900 dark:text-white">{{ $placaDetalle->dias_calculados }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-300">Factor:</span>
                                            <span
                                                class="text-gray-900 dark:text-white">{{ number_format($placaDetalle->factor_prorateo, 4) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-300">Monto:</span>
                                            <span class="font-bold text-blue-600 dark:text-blue-400">
                                                {{ $cobro->moneda }}
                                                {{ number_format($placaDetalle->monto_calculado, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <x-icon name="rectangle-group" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-gray-600 dark:text-gray-300">No hay placas específicas registradas</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Se usará la cantidad de placas genérica:
                            {{ $cobro->cantidad_placas }}</p>
                    </div>
                @endif

                <!-- Notas -->
                @if ($cobro->notas)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            <x-icon name="document-text" class="w-5 h-5 inline mr-2" />
                            Notas
                        </h3>
                        <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $cobro->notas }}</p>
                    </div>
                @endif

                <!-- Información de Auditoría -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        <x-icon name="clock" class="w-5 h-5 inline mr-2" />
                        Información de Auditoría
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Creado:</span>
                            <div class="text-gray-900 dark:text-white">
                                {{ $cobro->created_at->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600 dark:text-gray-300">Última actualización:</span>
                            <div class="text-gray-900 dark:text-white">
                                {{ $cobro->updated_at->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                    @if ($cobro->fecha_procesamiento)
                        <div class="mt-2">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Fecha Procesamiento:</span>
                            <div class="text-gray-900 dark:text-white">
                                {{ $cobro->fecha_procesamiento->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <x-slot name="footer" class="flex justify-end gap-x-4">
            <x-button flat label="Cerrar" wire:click="closeModal" />
        </x-slot>
    </x-modal-card>
</div>
