<?php

namespace App\Livewire\Recibos;

use Carbon\Carbon;
use App\Models\Recibo;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Http\Response;
use WireUi\Traits\WireUiActions;
use Livewire\WithoutUrlPagination;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;
    use WireUiActions;
    public string $search = '';
    public string $estadoFilter = 'todos';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public ?Recibo $selectedRecibo = null;
    public bool $isOpenDetalle = false;
    public bool $isOpenPago = false;
    public array $formPago = [
        'metodo_pago' => '',
        'numero_referencia' => '',
        'monto_pagado' => 0,
        'fecha_pago' => '',
        'observaciones' => ''
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'estadoFilter' => ['except' => 'todos'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    public function render()
    {
        return view('livewire.recibos.index', [
            'recibos' => $this->recibos,
            'estadosDisponibles' => [
                'todos' => 'Todos los Estados',
                'pendiente' => 'Pendientes',
                'pagado' => 'Pagados',
                'vencidos' => 'Vencidos',
                'anulado' => 'Anulados',
            ]
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->estadoFilter = 'todos';
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->selectedRecibo = null;
        $this->dispatch('openCreateForm');
    }

    public function editRecibo(Recibo $recibo): void
    {
        $this->selectedRecibo = $recibo;
        $this->dispatch('openEditForm', recibo: $recibo);
    }

    public function confirmDelete(Recibo $recibo): void
    {
        $this->selectedRecibo = $recibo;
        $this->dispatch('openDeleteModal', reciboId: $recibo->id);
    }

    public function verDetalle(Recibo $recibo): void
    {
        $this->selectedRecibo = $recibo->load('detalles');
        $this->isOpenDetalle = true;
    }

    public function abrirModalPago(Recibo $recibo): void
    {
        $this->selectedRecibo = $recibo;
        $this->formPago = [
            'metodo_pago' => '',
            'numero_referencia' => '',
            'monto_pagado' => $recibo->monto_recibo,
            'fecha_pago' => now()->format('Y-m-d'),
            'observaciones' => ''
        ];
        $this->isOpenPago = true;
    }

    public function marcarComoPagado(): void
    {
        $this->validate([
            'formPago.metodo_pago' => 'required|string|max:100',
            'formPago.monto_pagado' => 'required|numeric|min:0.01',
            'formPago.fecha_pago' => 'required|date|before_or_equal:today',
            'formPago.numero_referencia' => 'nullable|string|max:100',
            'formPago.observaciones' => 'nullable|string|max:500'
        ], [
            'formPago.metodo_pago.required' => 'El método de pago es obligatorio',
            'formPago.monto_pagado.required' => 'El monto pagado es obligatorio',
            'formPago.monto_pagado.min' => 'El monto debe ser mayor a 0',
            'formPago.fecha_pago.required' => 'La fecha de pago es obligatoria',
            'formPago.fecha_pago.before_or_equal' => 'La fecha no puede ser futura'
        ]);

        if ($this->selectedRecibo) {
            $this->selectedRecibo->marcarComoPagado([
                'metodo_pago' => $this->formPago['metodo_pago'],
                'numero_referencia' => $this->formPago['numero_referencia'],
                'monto_pagado' => $this->formPago['monto_pagado'],
                'fecha_pago' => $this->formPago['fecha_pago'],
                'observaciones' => $this->formPago['observaciones']
            ]);

            // Actualizar el período del cobro si es recurrente
            $this->actualizarPeriodoCobro($this->selectedRecibo);

            $this->isOpenPago = false;
            $this->selectedRecibo = null;
            $this->resetPage();

            $this->notification()->success('Recibo pagado', 'El recibo ha sido pagado correctamente.');
        }
    }

    private function actualizarPeriodoCobro(Recibo $recibo): void
    {
        $cobro = $recibo->cobro;

        if ($cobro && in_array($cobro->periodo_facturacion, ['Mensual', 'Bimensual', 'Trimestral', 'Semestral', 'Anual'])) {
            // Calcular las nuevas fechas según el período
            $fechaFinActual = Carbon::parse($cobro->fecha_fin_periodo);
            $nuevaFechaInicio = $fechaFinActual->addDay();

            // Calcular nueva fecha fin según el período
            $nuevaFechaFin = match ($cobro->periodo_facturacion) {
                'Mensual' => $nuevaFechaInicio->copy()->addMonth()->subDay(),
                'Bimensual' => $nuevaFechaInicio->copy()->addMonths(2)->subDay(),
                'Trimestral' => $nuevaFechaInicio->copy()->addMonths(3)->subDay(),
                'Semestral' => $nuevaFechaInicio->copy()->addMonths(6)->subDay(),
                'Anual' => $nuevaFechaInicio->copy()->addYear()->subDay(),
                default => $nuevaFechaInicio->copy()->addMonth()->subDay()
            };

            // Actualizar el período del cobro
            $cobro->update([
                'fecha_inicio_periodo' => $nuevaFechaInicio,
                'fecha_fin_periodo' => $nuevaFechaFin,
                'estado' => 'activo' // Reactivar para el siguiente período
            ]);

            // Actualizar el período de todas las placas del cobro
            $cobro->cobroPlacas()->update([
                'fecha_inicio' => $nuevaFechaInicio,
                'fecha_fin' => $nuevaFechaFin
            ]);
        }
    }

    #[On(['recibo-created', 'recibo-updated', 'recibo-deleted', 'refresh-recibos-list'])]
    public function refreshRecibos(): void
    {
        $this->selectedRecibo = null;
        $this->resetPage();
    }

    public function exportar(): Response
    {
        $recibos = $this->getRecibosQuery()->get();

        $csv = "Número,Cliente,Placa,Monto,Fecha Emisión,Fecha Vencimiento,Estado,Método Pago,Fecha Pago\n";

        foreach ($recibos as $recibo) {
            $clienteNombre = $recibo->data_cliente['nombre_cliente'] ?? 'N/A';
            $placa = $recibo->data_cobro['placa'] ?? 'N/A';

            $csv .= sprintf(
                "%s,%s,%s,%.2f,%s,%s,%s,%s,%s\n",
                $recibo->numero_recibo,
                $clienteNombre,
                $placa,
                $recibo->monto_recibo,
                $recibo->fecha_emision?->format('Y-m-d') ?? 'N/A',
                $recibo->fecha_vencimiento?->format('Y-m-d') ?? 'N/A',
                $recibo->estado_recibo ?? 'N/A',
                $recibo->metodo_pago ?? 'N/A',
                $recibo->fecha_pago?->format('Y-m-d') ?? 'N/A'
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="recibos-' . now()->format('Y-m-d') . '.csv"');
    }

    private function getRecibosQuery(): Builder
    {
        $query = Recibo::query();

        // Aplicar filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_recibo', 'like', '%' . $this->search . '%')
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.nombre_cliente')) LIKE ?", ['%' . $this->search . '%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.ruc_dni')) LIKE ?", ['%' . $this->search . '%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cobro, '$.placa')) LIKE ?", ['%' . $this->search . '%']);
            });
        }

        // Aplicar filtro de estado
        if ($this->estadoFilter !== 'todos') {
            if ($this->estadoFilter === 'vencidos') {
                $query->where('fecha_vencimiento', '<', now())
                    ->where('estado_recibo', '!=', 'pagado');
            } else {
                $query->where('estado_recibo', $this->estadoFilter);
            }
        }

        // Aplicar ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    public function getRecibosProperty()
    {
        return $this->getRecibosQuery()->paginate($this->perPage);
    }

    #[On('recibo-deleted')]
    public function refreshAfterDelete(): void
    {
        // Refrescar la lista después de eliminar
        $this->resetPage();
    }
}
