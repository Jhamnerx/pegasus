<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Reportes y Estadísticas</h1>
            <p class="text-gray-600 dark:text-gray-400">Análisis y reportes del sistema GPS</p>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Exportar/Imprimir -->
            <x-button secondary icon="printer" label="Imprimir" onclick="window.print()" />
            <x-button positive icon="arrow-down-tray" label="Exportar" />
        </div>
    </div>

    <!-- Controls -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-sm border border-gray-200 dark:border-gray-700 mb-8">
        <div class="px-5 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Tipo de Reporte -->
                <div>
                    <x-select label="Tipo de Reporte" wire:model.live="tipoReporte" :options="[
                        ['name' => 'Resumen de Pagos', 'id' => 'resumen-pagos'],
                        ['name' => 'Análisis de Atrasos', 'id' => 'analisis-atrasos'],
                        ['name' => 'Proyección de Ingresos', 'id' => 'proyeccion-ingresos'],
                    ]"
                        option-label="name" option-value="id" :clearable="false" />
                </div>

                <!-- Período -->
                <div>
                    <x-select label="Período" wire:model.live="periodo" :options="[
                        ['name' => 'Mes actual', 'id' => 'mes-actual'],
                        ['name' => 'Mes anterior', 'id' => 'mes-anterior'],
                        ['name' => 'Trimestre actual', 'id' => 'trimestre-actual'],
                        ['name' => 'Año actual', 'id' => 'año-actual'],
                    ]" option-label="name"
                        option-value="id" :clearable="false" />
                </div>

                <!-- Generar Reporte -->
                <div>
                    <x-button primary label="Generando..." wire:loading wire:target="generarReporte" icon="arrow-path"
                        disabled />
                    <x-button primary label="Generar Reporte" wire:loading.remove wire:target="generarReporte"
                        wire:click="generarReporte" icon="chart-bar-square" />
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido del Reporte -->
    <div>
        <!-- Loading Toast -->
        <div wire:loading wire:target="generarReporte"
            class="fixed top-4 right-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-sm font-medium">Generando reporte...</span>
        </div>

        <!-- Contenido sin afectar opacidad de gráficos -->
        <div wire:loading.class="pointer-events-none" wire:target="generarReporte">
            <!-- Overlay de carga solo para texto e interacciones -->
            <div wire:loading wire:target="generarReporte"
                class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 z-10 pointer-events-none">
            </div>

            <div class="relative">
                @if ($tipoReporte === 'resumen-pagos')
                    @include('livewire.reportes.resumen-pagos')
                @elseif($tipoReporte === 'analisis-atrasos')
                    @include('livewire.reportes.analisis-atrasos')
                @elseif($tipoReporte === 'proyeccion-ingresos')
                    @include('livewire.reportes.proyeccion-ingresos')
                @endif
            </div>
        </div>
    </div>

</div>
