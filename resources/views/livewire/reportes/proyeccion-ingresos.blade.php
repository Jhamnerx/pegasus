<!-- Proyección de Ingresos -->
<div class="space-y-6">
    <!-- Título del Reporte -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Proyección de Ingresos</h2>
        </div>

        <div class="p-6">
            <!-- Métricas principales -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Ingresos Actuales -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Ingresos Actuales</p>
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                S/{{ number_format($this->proyeccionIngresos['ingresos_actuales'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ingresos Proyectados -->
                <div class="bg-green-50 dark:bg-green-900/20 p-6 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Proyección Estimada</p>
                            <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                S/{{ number_format($this->proyeccionIngresos['proyeccion_estimada'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pendiente de Cobrar -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-6 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-yellow-100 dark:bg-yellow-800 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pendiente Cobrar</p>
                            <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                                S/{{ number_format($this->proyeccionIngresos['pendiente_cobrar'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Crecimiento Esperado -->
                <div class="bg-purple-50 dark:bg-purple-900/20 p-6 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Crecimiento</p>
                            <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                {{ number_format($this->proyeccionIngresos['porcentaje_crecimiento'] ?? 0, 1) }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Proyección Temporal -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Proyección Temporal de Ingresos
                </h3>
                <div class="relative" style="height: 400px;" x-data="{
                    initChart() {
                        window.initProyeccionTemporalChart({
                            periodos: @js(collect($this->proyeccionIngresos['detalle_periodos'] ?? [])->pluck('periodo')),
                            proyecciones: @js(collect($this->proyeccionIngresos['detalle_periodos'] ?? [])->pluck('proyeccion')),
                            actuales: @js(collect($this->proyeccionIngresos['detalle_periodos'] ?? [])->pluck('actual'))
                        });
                    }
                }" x-init="$nextTick(() => initChart())">>
                    <canvas id="proyeccionTemporalChart"></canvas>
                </div>
            </div>

            <!-- Detalle por Período -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Tabla Detallada -->
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Proyección por Período</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Período</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Proyección</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Variación</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->proyeccionIngresos['detalle_periodos'] ?? [] as $periodo)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $periodo['periodo'] }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            S/{{ number_format($periodo['proyeccion'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $variacion = $periodo['variacion'];
                                                $color = $variacion >= 0 ? 'green' : 'red';
                                                $icono = $variacion >= 0 ? '↑' : '↓';
                                            @endphp
                                            <span
                                                class="inline-flex items-center text-sm font-medium text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                                {{ $icono }} {{ number_format(abs($variacion), 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Sin datos de proyección
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Indicadores de Rendimiento -->
                <div class="space-y-6">
                    <!-- Tasa de Efectividad -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tasa de Efectividad</h3>
                        <div class="relative">
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cobros
                                    Realizados</span>
                                <span
                                    class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($this->proyeccionIngresos['tasa_efectividad'] ?? 0, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                    style="width: {{ $this->proyeccionIngresos['tasa_efectividad'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Promedio Mensual -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Promedio Mensual</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            S/{{ number_format($this->proyeccionIngresos['promedio_mensual'] ?? 0, 2) }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Basado en los últimos {{ $this->proyeccionIngresos['meses_calculo'] ?? 6 }} meses
                        </p>
                    </div>

                    <!-- Meta Proyectada -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Meta Proyectada</h3>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                            S/{{ number_format($this->proyeccionIngresos['meta_proyectada'] ?? 0, 2) }}
                        </p>
                        <div class="flex items-center mt-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Próximos 3 meses</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
