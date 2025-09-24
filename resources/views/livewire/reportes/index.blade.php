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
                        option-label="name" option-value="id" />
                </div>

                <!-- Período -->
                <div>
                    <x-select label="Período" wire:model.live="periodo" :options="[
                        ['name' => 'Mes actual', 'id' => 'mes-actual'],
                        ['name' => 'Mes anterior', 'id' => 'mes-anterior'],
                        ['name' => 'Trimestre actual', 'id' => 'trimestre-actual'],
                        ['name' => 'Año actual', 'id' => 'año-actual'],
                    ]" option-label="name"
                        option-value="id" />
                </div>

                <!-- Generar Reporte -->
                <div>
                    <x-button primary label="Generar Reporte" wire:click="generarReporte" icon="chart-bar-square"
                        wire:loading.attr="disabled" wire:target="generarReporte" />
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido del Reporte -->
    <div wire:loading.class="opacity-50" wire:target="generarReporte">
        @if ($tipoReporte === 'resumen-pagos')
            @include('livewire.reportes.resumen-pagos')
        @elseif($tipoReporte === 'analisis-atrasos')
            @include('livewire.reportes.analisis-atrasos')
        @elseif($tipoReporte === 'proyeccion-ingresos')
            @include('livewire.reportes.proyeccion-ingresos')
        @endif
    </div>

    <!-- Loading Indicator -->
    <div wire:loading wire:target="generarReporte"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-gray-700 dark:text-gray-300">Generando reporte...</span>
            </div>
        </div>
    </div>
</div>
