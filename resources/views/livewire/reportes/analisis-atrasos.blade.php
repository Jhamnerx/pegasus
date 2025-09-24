<!-- Análisis de Atrasos -->
<div class="space-y-6">
    <!-- Título del Reporte -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Análisis de Atrasos</h2>
        </div>

        <div class="p-6">
            <!-- Métricas principales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Cobros Atrasados -->
                <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-red-100 dark:bg-red-800 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">Cobros Atrasados</p>
                            <p class="text-3xl font-bold text-red-900 dark:text-red-100">
                                {{ $this->analisisAtrasos['cobros_atrasados'] ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Monto Atrasado -->
                <div class="bg-orange-50 dark:bg-orange-900/20 p-6 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 bg-orange-100 dark:bg-orange-800 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Monto Atrasado</p>
                            <p class="text-3xl font-bold text-orange-900 dark:text-orange-100">
                                S/{{ number_format($this->analisisAtrasos['monto_atrasado'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Distribución por Atraso (Gráfico) -->
                <div class="bg-gray-50 dark:bg-gray-900 p-6 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Distribución por Atraso</h3>
                    <div class="relative" x-data="{
                        initChart() {
                            window.initAnalisisAtrasosChart({
                                distribucion: {
                                    menos_15: {{ $this->analisisAtrasos['distribucion']['menos_15'] ?? 0 }},
                                    entre_15_30: {{ $this->analisisAtrasos['distribucion']['entre_15_30'] ?? 0 }},
                                    mas_30: {{ $this->analisisAtrasos['distribucion']['mas_30'] ?? 0 }}
                                }
                            });
                        }
                    }" x-init="$nextTick(() => initChart())">
                        <canvas id="distribucionAtrasosChart" width="200" height="100"></canvas>
                    </div>
                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Menos de 15 días:</span>
                            <span
                                class="font-semibold">{{ $this->analisisAtrasos['distribucion']['menos_15'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Entre 15 y 30 días:</span>
                            <span
                                class="font-semibold">{{ $this->analisisAtrasos['distribucion']['entre_15_30'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Más de 30 días:</span>
                            <span
                                class="font-semibold">{{ $this->analisisAtrasos['distribucion']['mas_30'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Cobros Atrasados -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detalle de Cobros Atrasados</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Cliente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Servicio</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Monto</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Vencimiento</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Atraso</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Contacto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->analisisAtrasos['detalle'] ?? [] as $detalle)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $detalle['cliente'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $detalle['servicio'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        S/{{ number_format($detalle['monto'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $detalle['fecha_vencimiento_str'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $dias = $detalle['dias_atraso'];
                                            $color = $dias <= 15 ? 'yellow' : ($dias <= 30 ? 'orange' : 'red');
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900 dark:text-{{ $color }}-200">
                                            {{ $dias }} días
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $detalle['contacto'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No hay cobros atrasados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
