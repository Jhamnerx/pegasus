<?php

namespace App\Livewire\Recibos;

use App\Exports\RecibosDetalladoExport;
use App\Exports\RecibosExport;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use WireUi\Traits\WireUiActions;

class Index extends Component
{
    use WireUiActions;
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public string $estadoFilter = 'todos';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

    public ?Recibo $selectedRecibo = null;

    public bool $isOpenDetalle = false;

    public bool $isOpenPago = false;

    public bool $isOpenExportModal = false;

    public array $formPago = [
        'metodo_pago' => '',
        'numero_referencia' => '',
        'monto_pagado' => 0,
        'fecha_pago' => '',
        'observaciones' => '',
    ];

    public array $exportFilters = [
        'cliente_id' => '',
        'estado' => 'todos',
        'fecha_desde' => '',
        'fecha_hasta' => '',
        'tipo_detalle' => 'resumido', // 'resumido' o 'detallado'
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
            ],
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
            'observaciones' => '',
        ];
        $this->isOpenPago = true;
    }

    public function abrirModalExport(): void
    {
        $this->exportFilters = [
            'cliente_id' => '',
            'estado' => 'todos',
            'fecha_desde' => '',
            'fecha_hasta' => '',
            'tipo_detalle' => 'resumido',
        ];
        $this->isOpenExportModal = true;
    }

    public function toggleWhatsAppNotification(int $reciboId): void
    {
        $recibo = Recibo::find($reciboId);

        if ($recibo && $recibo->estado_recibo === 'vencido') {
            $recibo->update([
                'enviado_whatsapp' => ! $recibo->enviado_whatsapp,
            ]);

            $estadoTexto = $recibo->enviado_whatsapp ? 'desactivadas' : 'activadas';
            $this->notification()->success(
                'Notificaciones WhatsApp',
                "Las notificaciones de WhatsApp han sido {$estadoTexto} para el recibo {$recibo->numero_recibo}."
            );
        }
    }

    public function marcarComoPagado(): void
    {
        $this->validate([
            'formPago.metodo_pago' => 'required|string|max:100',
            'formPago.monto_pagado' => 'required|numeric|min:0.01',
            'formPago.fecha_pago' => 'required|date|before_or_equal:today',
            'formPago.numero_referencia' => 'nullable|string|max:100',
            'formPago.observaciones' => 'nullable|string|max:500',
        ], [
            'formPago.metodo_pago.required' => 'El método de pago es obligatorio',
            'formPago.monto_pagado.required' => 'El monto pagado es obligatorio',
            'formPago.monto_pagado.min' => 'El monto debe ser mayor a 0',
            'formPago.fecha_pago.required' => 'La fecha de pago es obligatoria',
            'formPago.fecha_pago.before_or_equal' => 'La fecha no puede ser futura',
        ]);

        if ($this->selectedRecibo) {
            $this->selectedRecibo->marcarComoPagado([
                'metodo_pago' => $this->formPago['metodo_pago'],
                'numero_referencia' => $this->formPago['numero_referencia'],
                'monto_pagado' => $this->formPago['monto_pagado'],
                'fecha_pago' => $this->formPago['fecha_pago'],
                'observaciones' => $this->formPago['observaciones'],
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
                'estado' => 'activo', // Reactivar para el siguiente período
            ]);

            // Actualizar el período de todas las placas del cobro
            $cobro->cobroPlacas()->update([
                'fecha_inicio' => $nuevaFechaInicio,
                'fecha_fin' => $nuevaFechaFin,
            ]);
        }
    }

    #[On(['recibo-created', 'recibo-updated', 'recibo-deleted', 'refresh-recibos-list'])]
    public function refreshRecibos(): void
    {
        $this->selectedRecibo = null;
        $this->resetPage();
    }

    public function exportarExcel()
    {
        $this->validate([
            'exportFilters.fecha_desde' => 'nullable|date',
            'exportFilters.fecha_hasta' => 'nullable|date|after_or_equal:exportFilters.fecha_desde',
        ], [
            'exportFilters.fecha_hasta.after_or_equal' => 'La fecha hasta debe ser posterior o igual a la fecha desde',
        ]);

        // Crear nombre del archivo con timestamp
        $tipoDetalle = $this->exportFilters['tipo_detalle'] === 'detallado' ? '-detallado' : '';
        $filename = 'recibos-export'.$tipoDetalle.'-'.now()->format('Y-m-d-H-i').'.xlsx';

        // Cerrar modal
        // $this->isOpenExportModal = false;

        // Elegir la clase de exportación según el tipo de detalle
        $exportClass = $this->exportFilters['tipo_detalle'] === 'detallado'
            ? RecibosDetalladoExport::class
            : RecibosExport::class;

        // Exportar usando Laravel Excel directamente
        return Excel::download(
            new $exportClass(
                $this->exportFilters['cliente_id'],
                $this->exportFilters['estado'],
                $this->exportFilters['fecha_desde'],
                $this->exportFilters['fecha_hasta']
            ),
            $filename
        );
    }

    private function getRecibosQuery(): Builder
    {
        $query = Recibo::query();

        // Aplicar filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_recibo', 'like', '%'.$this->search.'%')
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.nombre_cliente')) LIKE ?", ['%'.$this->search.'%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.ruc_dni')) LIKE ?", ['%'.$this->search.'%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cobro, '$.placa')) LIKE ?", ['%'.$this->search.'%']);
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

    private function getExportQuery(): Builder
    {
        $query = Recibo::query();

        // Filtro por cliente
        if (! empty($this->exportFilters['cliente_id'])) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.id')) = ?", [$this->exportFilters['cliente_id']]);
        }

        // Filtro por estado
        if ($this->exportFilters['estado'] !== 'todos') {
            if ($this->exportFilters['estado'] === 'vencidos') {
                $query->where('fecha_vencimiento', '<', now())
                    ->where('estado_recibo', '!=', 'pagado');
            } else {
                $query->where('estado_recibo', $this->exportFilters['estado']);
            }
        }

        // Filtro por rango de fechas
        if (! empty($this->exportFilters['fecha_desde'])) {
            $query->where('fecha_emision', '>=', $this->exportFilters['fecha_desde']);
        }

        if (! empty($this->exportFilters['fecha_hasta'])) {
            $query->where('fecha_emision', '<=', $this->exportFilters['fecha_hasta']);
        }

        // Ordenar por fecha de emisión descendente
        $query->orderBy('fecha_emision', 'desc');

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
