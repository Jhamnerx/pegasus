<?php

namespace App\Livewire\Dashboard;

use App\Models\Cliente;
use App\Models\Cobro;
use App\Models\Recibo;
use App\Models\Servicio;
use Livewire\Component;

class Index extends Component
{
    public $estadisticas = [];

    public $cobrosRecientes = [];

    public $alertas = [];

    public $isLoading = false;

    public function mount(): void
    {
        $this->cargarDatosDashboard();
    }

    public function cargarDatosDashboard(): void
    {
        $this->isLoading = true;

        // Calcular estadísticas
        $totalClientes = Cliente::count();
        $totalCobros = Cobro::count();
        $totalRecibos = Recibo::count();
        $totalServicios = Servicio::count();

        // Montos usando la nueva estructura (recibos)
        $recibosPagados = Recibo::where('estado_recibo', 'pagado')->sum('monto_recibo');
        $recibosPendientes = Recibo::where('estado_recibo', 'pendiente')->sum('monto_recibo');
        $recibosVencidos = Recibo::where('estado_recibo', 'vencido')->sum('monto_recibo');

        $this->estadisticas = [
            [
                'titulo' => 'Total Clientes',
                'valor' => $totalClientes,
                'icono' => 'users',
                'color' => 'blue',
                'progreso' => min(100, ($totalClientes / 200) * 100), // Meta de 200 clientes
            ],
            [
                'titulo' => 'Recibos Pagados',
                'valor' => 'S/ ' . number_format($recibosPagados, 2),
                'icono' => 'cash',
                'color' => 'green',
                'progreso' => 85,
            ],
            [
                'titulo' => 'Recibos Pendientes',
                'valor' => 'S/ ' . number_format($recibosPendientes, 2),
                'icono' => 'clock',
                'color' => 'yellow',
                'progreso' => 65,
            ],
            [
                'titulo' => 'Recibos Vencidos',
                'valor' => 'S/ ' . number_format($recibosVencidos, 2),
                'icono' => 'exclamation-triangle',
                'color' => 'red',
                'progreso' => 45,
            ],
        ];

        // Recibos recientes (reemplaza cobros recientes)
        $this->cobrosRecientes = Recibo::latest()
            ->limit(5)
            ->get()
            ->map(function ($recibo) {
                $clienteNombre = $recibo->data_cliente['nombre_cliente'] ?? 'Sin cliente';
                $placa = $recibo->data_cobro['placa'] ?? 'Cobro general';

                return [
                    'id' => $recibo->id,
                    'numero_recibo' => $recibo->numero_recibo,
                    'cliente' => $clienteNombre,
                    'placa' => $placa,
                    'monto' => $recibo->monto_recibo,
                    'estado' => $recibo->estado_recibo,
                    'fecha_vencimiento' => $recibo->fecha_vencimiento,
                    'dias_vencimiento' => now()->diffInDays($recibo->fecha_vencimiento, false),
                    'esta_vencido' => $recibo->fecha_vencimiento < now() && $recibo->estado_recibo !== 'pagado',
                ];
            });

        // Generar alertas
        $this->generarAlertas();

        $this->isLoading = false;
    }

    private function generarAlertas(): void
    {
        $this->alertas = [];

        // Recibos vencidos
        $recibosVencidos = Recibo::where('estado_recibo', 'vencido')
            ->count();

        if ($recibosVencidos > 0) {
            $this->alertas[] = [
                'tipo' => 'error',
                'titulo' => 'Recibos Vencidos',
                'mensaje' => "Tienes {$recibosVencidos} recibos vencidos que requieren atención inmediata.",
                'accion' => 'Ver recibos',
            ];
        }

        // Recibos próximos a vencer (pendientes)
        $recibosProximosVencer = Recibo::where('estado_recibo', 'pendiente')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
            ->count();

        if ($recibosProximosVencer > 0) {
            $this->alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Recibos Próximos a Vencer',
                'mensaje' => "Tienes {$recibosProximosVencer} recibos que vencen en los próximos 7 días.",
                'accion' => 'Ver recibos',
            ];
        }

        // Cobros que no han generado recibos
        $cobrosActivos = Cobro::where('estado', 'activo')
            ->whereDoesntHave('recibos', function ($query) {
                $query->where('estado_recibo', 'pendiente');
            })
            ->count();

        if ($cobrosActivos > 0) {
            $this->alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Cobros Listos para Generar Recibos',
                'mensaje' => "Tienes {$cobrosActivos} cobros que pueden generar nuevos recibos.",
                'accion' => 'Generar recibos',
            ];
        }
    }

    public function verDetalleCobro($cobroId): void
    {
        $this->dispatch('open-cobro-detail', cobroId: $cobroId);
    }

    public function verDetalleRecibo($reciboId): void
    {
        $this->dispatch('open-recibo-detail', reciboId: $reciboId);
    }

    public function marcarComoPagado($reciboId): void
    {
        $recibo = Recibo::find($reciboId);
        if ($recibo) {
            $recibo->marcarComoPagado([
                'fecha_pago' => now(),
                'metodo_pago' => 'Manual desde Dashboard',
                'monto_pagado' => $recibo->monto_recibo,
            ]);
            $this->cargarDatosDashboard();
            $this->dispatch('recibo-updated', message: 'Recibo marcado como pagado exitosamente.');
        }
    }

    public function refrescarDatos(): void
    {
        $this->cargarDatosDashboard();
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
