<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

    <!-- Dashboard Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Bienvenido al sistema de gestión GPS - PEGASUS S.A.C.</p>
        </div>

        <div class="flex items-center space-x-3">
            <flux:button wire:click="refrescarDatos" variant="ghost" size="sm">
                <flux:icon.arrow-path class="w-4 h-4" />
                Actualizar
            </flux:button>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Loading State -->
    @if ($isLoading)
        <div class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span class="ml-2 text-gray-600 dark:text-gray-400">Cargando datos...</span>
        </div>
    @endif

    <!-- Alertas -->
    @if (!empty($alertas) && !$isLoading)
        <div class="mb-8 space-y-4">
            @foreach ($alertas as $alerta)
                <flux:callout variant="{{ $alerta['tipo'] === 'error' ? 'danger' : 'warning' }}" class="mb-4">
                    <strong>{{ $alerta['titulo'] }}</strong>
                    <p>{{ $alerta['mensaje'] }}</p>
                </flux:callout>
            @endforeach
        </div>
    @endif

    <!-- Estadísticas Cards -->
    @if (!$isLoading)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach ($estadisticas as $stat)
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $stat['titulo'] }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['valor'] }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-{{ $stat['color'] }}-100 dark:bg-{{ $stat['color'] }}-900">
                            @switch($stat['icono'])
                                @case('users')
                                    <flux:icon.users class="w-6 h-6 text-{{ $stat['color'] }}-600" />
                                @break

                                @case('cash')
                                    <flux:icon.banknotes class="w-6 h-6 text-{{ $stat['color'] }}-600" />
                                @break

                                @case('clock')
                                    <flux:icon.clock class="w-6 h-6 text-{{ $stat['color'] }}-600" />
                                @break

                                @case('exclamation-triangle')
                                    <flux:icon.exclamation-triangle class="w-6 h-6 text-{{ $stat['color'] }}-600" />
                                @break

                                @case('truck')
                                    <flux:icon.truck class="w-6 h-6 text-{{ $stat['color'] }}-600" />
                                @break
                            @endswitch
                        </div>
                    </div>
                    @if (isset($stat['progreso']))
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-{{ $stat['color'] }}-600 h-2 rounded-full"
                                    style="width: {{ $stat['progreso'] }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Recibos Recientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tabla de recibos recientes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recibos Recientes</h3>
                </div>
                <div class="p-6">
                    @if (count($recibosRecientes) > 0)
                        <div class="space-y-4">
                            @foreach ($recibosRecientes as $recibo)
                                <div
                                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $recibo['cliente'] }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $recibo['numero_recibo'] }} - {{ $recibo['placa'] }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Vence:
                                            {{ \Carbon\Carbon::parse($recibo['fecha_vencimiento'])->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 dark:text-white">S/
                                            {{ number_format($recibo['monto'], 2) }}</p>
                                        <flux:badge
                                            variant="{{ $recibo['estado'] === 'pagado' ? 'primary' : ($recibo['esta_vencido'] ? 'danger' : 'warning') }}"
                                            size="sm">
                                            {{ ucfirst($recibo['estado']) }}
                                        </flux:badge>
                                        @if ($recibo['estado'] === 'pendiente' && !$recibo['esta_vencido'])
                                            <div class="mt-1">
                                                <flux:button wire:click="marcarComoPagado({{ $recibo['id'] }})"
                                                    variant="primary" size="xs">
                                                    Marcar Pagado
                                                </flux:button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-center">
                            <flux:button variant="ghost" size="sm"
                                href="{{ route('recibos.index') ?? route('cobros.index') }}">
                                Ver todos los recibos
                            </flux:button>
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">No hay recibos recientes</p>
                    @endif
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Acciones Rápidas</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:button href="{{ route('clientes.index') }}" variant="outline" class="w-full">
                            <flux:icon.user-plus class="w-4 h-4 mr-2" />
                            Nuevo Cliente
                        </flux:button>

                        <flux:button href="{{ route('cobros.index') }}" variant="outline" class="w-full">
                            <flux:icon.banknotes class="w-4 h-4 mr-2" />
                            Registrar Cobro
                        </flux:button>

                        <flux:button href="{{ route('reportes.index') }}" variant="outline" class="w-full">
                            <flux:icon.chart-bar class="w-4 h-4 mr-2" />
                            Ver Reportes
                        </flux:button>

                        <flux:button href="{{ route('servicios.index') }}" variant="outline" class="w-full">
                            <flux:icon.truck class="w-4 h-4 mr-2" />
                            Ver Servicios
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
