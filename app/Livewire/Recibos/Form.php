<?php

namespace App\Livewire\Recibos;

use App\Models\Cliente;
use App\Models\Cobro;
use App\Models\Recibo;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Form extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?Recibo $recibo = null;

    public bool $isEditing = false;

    // Campos principales
    public string $numero_recibo = '';

    public ?int $cobro_id = null;

    public ?int $cliente_id = null;

    public ?int $servicio_id = null;

    public string $monto_recibo = '';

    public string $fecha_emision = '';

    public string $fecha_vencimiento = '';

    public string $estado_recibo = 'pendiente';

    // Campos de pago
    public string $fecha_pago = '';

    public string $metodo_pago = '';

    public string $numero_referencia = '';

    public string $monto_pagado = '';

    // Campos adicionales
    public string $observaciones = '';

    public string $moneda = 'PEN';

    // Datos de solo lectura extraídos de las relaciones
    public array $dataCliente = [];

    public array $dataServicio = [];

    public array $dataCobro = [];

    // Colecciones para selects
    public Collection $cobros;

    public Collection $clientes;

    public Collection $servicios;

    protected array $rules = [
        'numero_recibo' => 'required|string|max:20',
        'cobro_id' => 'nullable|exists:cobros,id',
        'cliente_id' => 'required|exists:clientes,id',
        'servicio_id' => 'required|exists:servicios,id',
        'monto_recibo' => 'required|numeric|min:0|max:999999.99',
        'fecha_emision' => 'required|date',
        'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
        'estado_recibo' => 'required|in:pendiente,pagado,vencido,anulado',
        'fecha_pago' => 'nullable|date',
        'metodo_pago' => 'nullable|string|max:50',
        'numero_referencia' => 'nullable|string|max:100',
        'monto_pagado' => 'nullable|numeric|min:0|max:999999.99',
        'observaciones' => 'nullable|string|max:1000',
        'moneda' => 'required|string|size:3',
    ];

    protected array $messages = [
        'numero_recibo.required' => 'El número de recibo es obligatorio.',
        'cliente_id.required' => 'Debe seleccionar un cliente.',
        'servicio_id.required' => 'Debe seleccionar un servicio.',
        'monto_recibo.required' => 'El monto del recibo es obligatorio.',
        'monto_recibo.numeric' => 'El monto debe ser un número válido.',
        'monto_recibo.min' => 'El monto no puede ser negativo.',
        'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
        'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
        'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior a la fecha de emisión.',
    ];

    public function mount(?Recibo $recibo = null): void
    {
        if ($recibo && $recibo->exists) {
            $this->recibo = $recibo;
            $this->fillForm();
        } else {
            $this->initializeDefaults();
        }

        $this->loadCollections();
    }

    public function render()
    {
        return view('livewire.recibos.form', [
            'estadosDisponibles' => [
                'pendiente' => 'Pendiente',
                'pagado' => 'Pagado',
                'vencido' => 'Vencido',
                'anulado' => 'Anulado',
            ],
            'metodosPago' => [
                'efectivo' => 'Efectivo',
                'transferencia' => 'Transferencia Bancaria',
                'tarjeta' => 'Tarjeta de Crédito/Débito',
                'cheque' => 'Cheque',
                'yape' => 'Yape',
                'plin' => 'Plin',
                'otro' => 'Otro',
            ],
            'monedasDisponibles' => [
                'PEN' => 'Soles (PEN)',
                'USD' => 'Dólares (USD)',
            ],
        ]);
    }

    #[On('openCreateForm')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isOpen = true;
    }

    #[On('openEditForm')]
    public function openEdit(Recibo $recibo): void
    {
        $this->resetForm();
        $this->recibo = $recibo;
        $this->isEditing = true;
        $this->fillForm();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->recibo = null;
        $this->numero_recibo = '';
        $this->cobro_id = null;
        $this->cliente_id = null;
        $this->servicio_id = null;
        $this->monto_recibo = '';
        $this->fecha_emision = '';
        $this->fecha_vencimiento = '';
        $this->estado_recibo = 'pendiente';
        $this->fecha_pago = '';
        $this->metodo_pago = '';
        $this->numero_referencia = '';
        $this->monto_pagado = '';
        $this->observaciones = '';
        $this->moneda = 'PEN';
        $this->dataCliente = [];
        $this->dataServicio = [];
        $this->dataCobro = [];
        $this->resetValidation();
    }

    private function fillForm(): void
    {
        if ($this->recibo) {
            $this->numero_recibo = $this->recibo->numero_recibo;
            $this->cobro_id = $this->recibo->cobro_id;
            $this->cliente_id = $this->recibo->cliente_id;
            $this->servicio_id = $this->recibo->servicio_id;
            $this->monto_recibo = (string) $this->recibo->monto_recibo;
            $this->fecha_emision = $this->recibo->fecha_emision?->format('Y-m-d') ?? '';
            $this->fecha_vencimiento = $this->recibo->fecha_vencimiento?->format('Y-m-d') ?? '';
            $this->estado_recibo = $this->recibo->estado_recibo;
            $this->fecha_pago = $this->recibo->fecha_pago?->format('Y-m-d') ?? '';
            $this->metodo_pago = $this->recibo->metodo_pago ?? '';
            $this->numero_referencia = $this->recibo->numero_referencia ?? '';
            $this->monto_pagado = (string) ($this->recibo->monto_pagado ?? '');
            $this->observaciones = $this->recibo->observaciones ?? '';
            $this->moneda = $this->recibo->moneda ?? 'PEN';

            // Cargar datos JSON
            $this->dataCliente = $this->recibo->dataCliente ?? [];
            $this->dataServicio = $this->recibo->dataServicio ?? [];
            $this->dataCobro = $this->recibo->dataCobro ?? [];
        }
    }

    private function initializeDefaults(): void
    {
        $this->fecha_emision = now()->format('Y-m-d');
        $this->fecha_vencimiento = now()->addDays(30)->format('Y-m-d');
        $this->numero_recibo = $this->generateNextNumeroRecibo();
    }

    private function loadCollections(): void
    {
        $this->cobros = Cobro::query()
            ->with(['cliente', 'servicio'])
            ->where('estado', 'procesado')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->clientes = Cliente::query()
            ->where('estado', 'Activo')
            ->orderBy('nombre_cliente')
            ->get();

        $this->servicios = Servicio::query()
            ->where('activo', true)
            ->orderBy('nombre_servicio')
            ->get();
    }

    public function updatedClienteId(): void
    {
        if ($this->cliente_id) {
            $cliente = Cliente::find($this->cliente_id);
            if ($cliente) {
                $this->dataCliente = [
                    'id' => $cliente->id,
                    'nombre_cliente' => $cliente->nombre_cliente,
                    'ruc_dni' => $cliente->ruc_dni,
                    'telefono' => $cliente->telefono,
                    'correo_electronico' => $cliente->correo_electronico,
                    'direccion' => $cliente->direccion,
                    'estado' => $cliente->estado,
                ];
            }
        }
    }

    public function updatedServicioId(): void
    {
        if ($this->servicio_id) {
            $servicio = Servicio::find($this->servicio_id);
            if ($servicio) {
                $this->dataServicio = [
                    'id' => $servicio->id,
                    'nombre_servicio' => $servicio->nombre_servicio,
                    'descripcion' => $servicio->descripcion,
                    'precio_base' => $servicio->precio_base,
                    'activo' => $servicio->activo,
                ];

                // Auto-calcular monto si no está establecido
                if (empty($this->monto_recibo)) {
                    $this->monto_recibo = (string) $servicio->precio_base;
                }
            }
        }
    }

    public function updatedCobroId(): void
    {
        if ($this->cobro_id) {
            $cobro = Cobro::with(['cliente', 'servicio'])->find($this->cobro_id);
            if ($cobro) {
                // Auto-llenar campos desde el cobro
                $this->cliente_id = $cobro->cliente_id;
                $this->servicio_id = $cobro->servicio_id;

                // Calcular monto total basado en cantidad de placas
                $montoTotal = $cobro->monto_base * ($cobro->cantidad_placas ?? 1);
                $this->monto_recibo = (string) $montoTotal;

                $this->dataCobro = [
                    'id' => $cobro->id,
                    'cantidad_placas' => $cobro->cantidad_placas ?? 1,
                    'monto_base' => $cobro->monto_base,
                    'monto_total' => $montoTotal,
                    'fecha_inicio_periodo' => $cobro->fecha_inicio_periodo?->format('Y-m-d'),
                    'fecha_fin_periodo' => $cobro->fecha_fin_periodo?->format('Y-m-d'),
                    'periodo_facturacion' => $cobro->periodo_facturacion,
                    'estado' => $cobro->estado,
                ];

                // Llenar datos del cliente y servicio
                $this->updatedClienteId();
                $this->updatedServicioId();
            }
        }
    }

    public function updatedEstadoRecibo(): void
    {
        if ($this->estado_recibo === 'pagado' && empty($this->fecha_pago)) {
            $this->fecha_pago = now()->format('Y-m-d');
            if (empty($this->monto_pagado)) {
                $this->monto_pagado = $this->monto_recibo;
            }
        }

        if ($this->estado_recibo !== 'pagado') {
            $this->fecha_pago = '';
            $this->monto_pagado = '';
            $this->metodo_pago = '';
            $this->numero_referencia = '';
        }
    }

    private function generateNextNumeroRecibo(): string
    {
        $lastRecibo = Recibo::orderBy('numero_recibo', 'desc')->first();
        $nextNumber = $lastRecibo ? intval($lastRecibo->numero_recibo) + 1 : 1;

        return str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'numero_recibo' => $this->numero_recibo,
                'cobro_id' => $this->cobro_id,
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'monto_recibo' => floatval($this->monto_recibo),
                'fecha_emision' => $this->fecha_emision,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'estado_recibo' => $this->estado_recibo,
                'fecha_pago' => $this->fecha_pago ?: null,
                'metodo_pago' => $this->metodo_pago ?: null,
                'numero_referencia' => $this->numero_referencia ?: null,
                'monto_pagado' => $this->monto_pagado ? floatval($this->monto_pagado) : null,
                'observaciones' => $this->observaciones,
                'data_cliente' => $this->dataCliente,
                'data_servicio' => $this->dataServicio,
                'data_cobro' => $this->dataCobro,
                'moneda' => $this->moneda,
                'usuario_generador_id' => Auth::id(),
            ];

            if ($this->recibo && $this->recibo->exists) {
                $this->recibo->update($data);
                $this->notification()->success('Recibo actualizado', 'El recibo ha sido actualizado correctamente.');
                $this->dispatch('recibo-updated', $this->recibo->id);
            } else {
                $newRecibo = Recibo::create($data);
                // Crear snapshots automáticamente si no se proporcionaron
                if (empty($this->dataCliente) || empty($this->dataServicio) || empty($this->dataCobro)) {
                    $newRecibo->crearSnapshots();
                }
                $this->notification()->success('Recibo creado', 'El recibo ha sido creado correctamente.');
                $this->dispatch('recibo-created', $newRecibo->id);
            }

            $this->closeModal();
            $this->dispatch('refresh-recibos-list');
        } catch (\Exception $e) {
            $this->notification()->error('Error', 'Error al guardar el recibo: '.$e->getMessage());
        }
    }

    public function cancel(): void
    {
        $this->closeModal();
    }
}
