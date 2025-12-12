<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Index extends Component
{
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public string $estadoFilter = 'all';

    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'estadoFilter' => ['except' => 'all'],
        'perPage' => ['except' => 15],
    ];

    public function render()
    {
        $servicios = Servicio::query()
            ->when($this->search, function ($query) {
                $query->where('nombre_servicio', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->when($this->estadoFilter === 'activo', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->estadoFilter === 'inactivo', function ($query) {
                $query->where('activo', false);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.servicios.index', [
            'servicios' => $servicios,
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

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->estadoFilter = 'all';
        $this->perPage = 10;
        $this->resetPage();
    }

    #[On('serviciosSaved')]
    public function refreshServicios(): void
    {
        $this->resetPage();
    }

    #[On('servicioDeleted')]
    public function refreshAfterDelete(): void
    {
        $this->resetPage();
    }
}
