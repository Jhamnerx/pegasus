<?php

namespace App\Livewire\Cobros;

use App\Models\Cobro;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Index extends Component
{
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public string $estadoFilter = 'all';

    public string $clienteFilter = '';

    public $periodoFilter = '';

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'estadoFilter' => ['except' => 'all'],
        'clienteFilter' => ['except' => ''],
        'periodoFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function render()
    {
        return view('livewire.cobros.index', [
            'cobros' => $this->getCobrosProperty(),
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

    public function updatingClienteFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodoFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->estadoFilter = 'all';
        $this->clienteFilter = '';
        $this->periodoFilter = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[On('cobrosSaved')]
    public function refreshCobros(): void
    {
        $this->resetPage();
    }

    #[On('cobroDeleted')]
    public function refreshAfterDelete(): void
    {
        $this->resetPage();
    }

    public function getCobrosProperty()
    {
        return Cobro::query()
            ->with(['cliente', 'servicio', 'cobroPlacas'])
            ->when($this->search, function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->where('nombre_cliente', 'like', '%'.$this->search.'%')
                        ->orWhere('ruc_dni', 'like', '%'.$this->search.'%');
                })
                    ->orWhereHas('servicio', function ($q) {
                        $q->where('nombre_servicio', 'like', '%'.$this->search.'%');
                    })
                    ->orWhere('descripcion_servicio_personalizado', 'like', '%'.$this->search.'%')
                    ->orWhere('notas', 'like', '%'.$this->search.'%');
            })
            ->when($this->estadoFilter !== 'all', function ($query) {
                $query->where('estado', $this->estadoFilter);
            })
            ->when($this->clienteFilter, function ($query) {
                $query->where('cliente_id', $this->clienteFilter);
            })
            ->when($this->periodoFilter, function ($query) {
                if ($this->periodoFilter === 'actual') {
                    $query->whereMonth('fecha_fin_periodo', now()->month)
                        ->whereYear('fecha_fin_periodo', now()->year);
                } elseif ($this->periodoFilter === 'anterior') {
                    $query->whereMonth('fecha_fin_periodo', now()->subMonth()->month)
                        ->whereYear('fecha_fin_periodo', now()->subMonth()->year);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }
}
