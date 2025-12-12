<?php

namespace App\Livewire\Reportes;

use App\Models\Cobro;
use App\Models\Recibo;
use Carbon\Carbon;
use Livewire\Component;

class Index extends Component
{
    public string $tipoReporte = 'resumen-pagos';

    public string $periodo = 'mes-actual';

    public function mount()
    {
        // Método vacío, los datos se calculan dinámicamente
    }

    public function render()
    {
        return view('livewire.reportes.index');
    }

    public function generarReporte()
    {
        // Método para trigger actualización sin hacer nada
        // Los datos se calculan en las propiedades computadas
    }

    // Usaremos propiedades computadas para evitar problemas de serialización
    public function getResumenPagosProperty()
    {
        $fechas = $this->obtenerFechasPeriodo();

        // Obtener recibos del período
        $recibos = Recibo::whereBetween('fecha_emision', [$fechas['inicio'], $fechas['fin']])->get();

        $totalCobros = $recibos->count();
        $montoTotal = $recibos->sum('monto_recibo');
        $cobrosPagados = $recibos->where('estado_recibo', 'pagado')->count();
        $montoCobrado = $recibos->where('estado_recibo', 'pagado')->sum('monto_recibo');
        $montoPendiente = $montoTotal - $montoCobrado;

        $porcentajePagados = $totalCobros > 0 ? round(($cobrosPagados / $totalCobros) * 100, 2) : 0;
        $porcentajeCobrado = $montoTotal > 0 ? round(($montoCobrado / $montoTotal) * 100, 2) : 0;

        return [
            'periodo' => $fechas['label'],
            'total_cobros' => $totalCobros,
            'monto_total' => $montoTotal,
            'cobros_pagados' => $cobrosPagados,
            'porcentaje_pagados' => $porcentajePagados,
            'monto_cobrado' => $montoCobrado,
            'porcentaje_cobrado' => $porcentajeCobrado,
            'monto_pendiente' => $montoPendiente,
            'monto_emitido' => $montoTotal,
        ];
    }

    public function getAnalisisAtrasosProperty()
    {
        $hoy = Carbon::now();

        // Obtener recibos vencidos (no pagados y fecha vencimiento < hoy)
        $recibosAtrasados = Recibo::where('fecha_vencimiento', '<', $hoy)
            ->where('estado_recibo', '!=', 'pagado')
            ->get();

        $cobrosAtrasados = $recibosAtrasados->count();
        $montoAtrasado = $recibosAtrasados->sum('monto_recibo');

        // Distribución por días de atraso
        $distribucion = [
            'menos_15' => 0,
            'entre_15_30' => 0,
            'mas_30' => 0,
        ];

        foreach ($recibosAtrasados as $recibo) {
            $diasAtraso = $hoy->diffInDays($recibo->fecha_vencimiento);
            if ($diasAtraso <= 15) {
                $distribucion['menos_15']++;
            } elseif ($diasAtraso <= 30) {
                $distribucion['entre_15_30']++;
            } else {
                $distribucion['mas_30']++;
            }
        }

        // Detalle de cobros atrasados (con datos del cliente)
        $detalleAtrasados = $recibosAtrasados->map(function ($recibo) use ($hoy) {
            // Verificar si data_cliente es array o null
            $cliente = is_array($recibo->data_cliente) ? $recibo->data_cliente : [];
            $servicio = is_array($recibo->data_servicio) ? $recibo->data_servicio : [];

            return [
                'cliente' => $cliente['nombre_cliente'] ?? 'N/A',
                'servicio' => $servicio['nombre_servicio'] ?? 'N/A',
                'monto' => $recibo->monto_recibo,
                'fecha_vencimiento_str' => $recibo->fecha_vencimiento->format('d/m/Y'),
                'dias_atraso' => $hoy->diffInDays($recibo->fecha_vencimiento),
                'contacto' => $cliente['telefono'] ?? 'N/A',
            ];
        });

        return [
            'cobros_atrasados' => $cobrosAtrasados,
            'monto_atrasado' => $montoAtrasado,
            'distribucion' => $distribucion,
            'detalle' => $detalleAtrasados->toArray(),
        ];
    }

    public function getProyeccionIngresosProperty()
    {
        $fechas = $this->obtenerFechasPeriodo();

        // Obtener ingresos actuales del período
        $ingresosActuales = Recibo::whereBetween('fecha_emision', [$fechas['inicio'], $fechas['fin']])
            ->where('estado_recibo', 'pagado')
            ->sum('monto_recibo');

        // Obtener cobros pendientes del período
        $pendienteCobrar = Recibo::whereBetween('fecha_emision', [$fechas['inicio'], $fechas['fin']])
            ->where('estado_recibo', '!=', 'pagado')
            ->sum('monto_recibo');

        // Calcular proyección basada en cobros activos
        $cobrosActivos = Cobro::where('estado', 'activo')->get();

        $proyeccionEstimada = 0;
        $detallePeriodos = [];

        // Generar proyección por los próximos 6 meses
        for ($i = 0; $i < 6; $i++) {
            $fechaPeriodo = Carbon::now()->addMonths($i);
            $mesPeriodo = $fechaPeriodo->format('F Y');

            $montoPeriodo = 0;
            foreach ($cobrosActivos as $cobro) {
                // Estimar facturación mensual basada en periodicidad
                $montoPeriodo += match ($cobro->periodicidad) {
                    'mensual' => $cobro->monto_servicio,
                    'bimensual' => $cobro->monto_servicio / 2,
                    'trimestral' => $cobro->monto_servicio / 3,
                    'semestral' => $cobro->monto_servicio / 6,
                    'anual' => $cobro->monto_servicio / 12,
                    default => $cobro->monto_servicio,
                };
            }

            $proyeccionEstimada += $montoPeriodo;
            $detallePeriodos[] = [
                'periodo' => $mesPeriodo,
                'proyeccion' => $montoPeriodo,
                'actual' => $i === 0 ? $ingresosActuales : 0, // Solo mostrar actual para el primer período
                'variacion' => $i > 0 && $detallePeriodos[$i - 1]['proyeccion'] > 0
                    ? (($montoPeriodo - $detallePeriodos[$i - 1]['proyeccion']) / $detallePeriodos[$i - 1]['proyeccion']) * 100
                    : 0,
            ];
        }

        // Calcular métricas adicionales
        $porcentajeCrecimiento = $ingresosActuales > 0 ? (($proyeccionEstimada - $ingresosActuales) / $ingresosActuales) * 100 : 0;
        $tasaEfectividad = $ingresosActuales + $pendienteCobrar > 0 ? ($ingresosActuales / ($ingresosActuales + $pendienteCobrar)) * 100 : 0;
        $promedioMensual = $proyeccionEstimada / 6;
        $metaProyectada = $promedioMensual * 3; // Meta para próximos 3 meses

        return [
            'ingresos_actuales' => $ingresosActuales,
            'proyeccion_estimada' => $proyeccionEstimada,
            'pendiente_cobrar' => $pendienteCobrar,
            'porcentaje_crecimiento' => $porcentajeCrecimiento,
            'detalle_periodos' => $detallePeriodos,
            'tasa_efectividad' => $tasaEfectividad,
            'promedio_mensual' => $promedioMensual,
            'meses_calculo' => 6,
            'meta_proyectada' => $metaProyectada,
        ];
    }

    private function obtenerFechasPeriodo(): array
    {
        return match ($this->periodo) {
            'mes-actual' => [
                'inicio' => Carbon::now()->startOfMonth(),
                'fin' => Carbon::now()->endOfMonth(),
                'label' => 'Mes Actual ('.Carbon::now()->startOfMonth()->format('d/m/Y').' - '.Carbon::now()->endOfMonth()->format('d/m/Y').')',
            ],
            'mes-anterior' => [
                'inicio' => Carbon::now()->subMonth()->startOfMonth(),
                'fin' => Carbon::now()->subMonth()->endOfMonth(),
                'label' => 'Mes Anterior',
            ],
            'trimestre-actual' => [
                'inicio' => Carbon::now()->startOfQuarter(),
                'fin' => Carbon::now()->endOfQuarter(),
                'label' => 'Trimestre Actual',
            ],
            'año-actual' => [
                'inicio' => Carbon::now()->startOfYear(),
                'fin' => Carbon::now()->endOfYear(),
                'label' => 'Año Actual',
            ],
            default => [
                'inicio' => Carbon::now()->startOfMonth(),
                'fin' => Carbon::now()->endOfMonth(),
                'label' => 'Mes Actual',
            ],
        };
    }
}
