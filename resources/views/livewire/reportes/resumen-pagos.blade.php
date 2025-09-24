<!-- Resumen de Pagos -->
<div class="space-y-6">
    <!-- Título del Reporte -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Resumen de Pagos</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Período: {{ $this->resumenPagos['periodo'] ?? 'N/A' }}
            </p>
        </div>

        <!-- Métricas Principales -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Total Cobros -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Total Cobros</p>
                            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">
                                {{ $this->resumenPagos['total_cobros'] ?? 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Monto Total -->
                <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-1">Monto Total</p>
                            <p class="text-3xl font-bold text-indigo-900 dark:text-indigo-100">
                                S/{{ number_format($this->resumenPagos['monto_total'] ?? 0, 2) }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-indigo-100 dark:bg-indigo-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Cobros Pagados -->
                <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Cobros Pagados</p>
                            <p class="text-3xl font-bold text-green-900 dark:text-green-100">
                                {{ $this->resumenPagos['cobros_pagados'] ?? 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-green-700 dark:text-green-300">
                            {{ number_format($this->resumenPagos['porcentaje_pagados'] ?? 0, 2) }}%
                        </p>
                        <p class="text-xl font-semibold text-green-900 dark:text-green-100">
                            S/{{ number_format($this->resumenPagos['monto_cobrado'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Estado de Cobros (Gráfico) -->
                <div class="bg-gray-50 dark:bg-gray-900 p-6 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Estado de Cobros</h3>

                    <!-- Indicador de carga para el gráfico -->
                    <div wire:loading wire:target="generarReporte" class="flex items-center justify-center h-40">
                        <div class="text-center">
                            <svg class="animate-spin h-6 w-6 text-blue-600 mx-auto mb-2" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Cargando...</p>
                        </div>
                    </div>

                    <!-- Gráfico que se muestra cuando no está cargando -->
                    <div wire:loading.remove wire:target="generarReporte">
                        <div class="relative" x-data="{
                            initChart() {
                                window.initResumenPagosChart({
                                    cobros_pagados: {{ $this->resumenPagos['cobros_pagados'] ?? 0 }},
                                    total_cobros: {{ $this->resumenPagos['total_cobros'] ?? 0 }}
                                });
                            }
                        }" x-init="$nextTick(() => initChart())">
                            <canvas id="estadoCobrosChart" width="150" height="150"></canvas>
                        </div>

                        <!-- Leyenda estática que no se ve afectada por la opacidad -->
                        <div class="mt-4 flex justify-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Pagados</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Pendientes</span>
                            </div>
                        </div>
                    </div>
                </div> <!-- Desglose de Montos -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-6 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Desglose de Montos</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Monto Emitido</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                S/{{ number_format($this->resumenPagos['monto_emitido'] ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Monto Cobrado</span>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                S/{{ number_format($this->resumenPagos['monto_cobrado'] ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Monto Pendiente</span>
                            <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                S/{{ number_format($this->resumenPagos['monto_pendiente'] ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
