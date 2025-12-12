<?php

namespace App\Livewire\Cobros;

use App\Models\Cobro;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class Form extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?Cobro $cobro = null;

    public bool $isEditing = false;

    // Propiedades del formulario
    public ?int $cliente_id = null;

    public ?int $servicio_id = null;

    public string $descripcion_servicio_personalizado = '';

    public float $monto_base = 0.0;

    public int $cantidad_placas = 1;

    public float $monto_unitario = 0.0;

    public string $periodo_facturacion = 'Mensual';

    public string $moneda = 'PEN';

    public string $fecha_inicio_periodo = '';

    public string $fecha_fin_periodo = '';

    public int $dias_para_vencimiento = 30;

    public string $estado = 'activo';

    public string $notas = '';

    // Para gestión de placas
    public Collection $placas;

    public string $nueva_placa = '';

    public float $monto_total_calculado = 0.0;

    // Para los selects
    public array $clienteOptions = [];

    public array $servicioOptions = [];

    protected function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'descripcion_servicio_personalizado' => 'nullable|string|max:255',
            'monto_base' => 'required|numeric|min:0.01|max:99999.99',
            'cantidad_placas' => 'required|integer|min:1|max:1000',
            'monto_unitario' => 'nullable|numeric|min:0|max:99999.99',
            'periodo_facturacion' => 'required|in:Mensual,Bimensual,Trimestral,Semestral,Anual',
            'moneda' => 'required|in:PEN,USD',
            'fecha_inicio_periodo' => 'required|date_format:d-m-Y',
            'fecha_fin_periodo' => 'required|date_format:d-m-Y|after:fecha_inicio_periodo',
            'dias_para_vencimiento' => 'required|integer|min:1|max:365',
            'estado' => 'required|in:activo,procesado,anulado',
            'notas' => 'nullable|string|max:1000',
            'nueva_placa' => 'nullable|string|max:20|regex:/^[A-Z0-9-]+$/',
            'placas' => 'required|array|min:1',
            'placas.*.placa' => 'required|string|max:20|regex:/^[A-Z0-9-]+$/',
            'placas.*.monto_calculado' => 'required|numeric|min:0.01',
            'placas.*.fecha_inicio' => 'required|date_format:d-m-Y',
            'placas.*.fecha_fin' => 'required|date_format:d-m-Y|after_or_equal:placas.*.fecha_inicio',
        ];
    }

    protected function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            'servicio_id.required' => 'Debe seleccionar un servicio.',
            'servicio_id.exists' => 'El servicio seleccionado no es válido.',
            'monto_base.required' => 'El monto base es obligatorio.',
            'monto_base.numeric' => 'El monto base debe ser un número válido.',
            'monto_base.min' => 'El monto base debe ser mayor a 0.',
            'monto_base.max' => 'El monto base no puede exceder S/ 99,999.99.',
            'periodo_facturacion.required' => 'Debe seleccionar un período de facturación.',
            'periodo_facturacion.in' => 'El período de facturación seleccionado no es válido.',
            'moneda.required' => 'Debe seleccionar una moneda.',
            'moneda.in' => 'La moneda seleccionada no es válida.',
            'fecha_inicio_periodo.required' => 'La fecha de inicio del período es obligatoria.',
            'fecha_inicio_periodo.date_format' => 'La fecha de inicio debe tener el formato DD-MM-YYYY.',
            'fecha_fin_periodo.required' => 'La fecha de fin del período es obligatoria.',
            'fecha_fin_periodo.date_format' => 'La fecha de fin debe tener el formato DD-MM-YYYY.',
            'fecha_fin_periodo.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'dias_para_vencimiento.required' => 'Los días para vencimiento son obligatorios.',
            'dias_para_vencimiento.integer' => 'Los días para vencimiento deben ser un número entero.',
            'dias_para_vencimiento.min' => 'Los días para vencimiento deben ser al menos 1.',
            'dias_para_vencimiento.max' => 'Los días para vencimiento no pueden exceder 365.',
            'estado.required' => 'Debe seleccionar un estado.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'nueva_placa.regex' => 'El formato de placa no es válido. Use letras, números y guiones.',
            'placas.required' => 'Debe agregar al menos una placa.',
            'placas.min' => 'Debe agregar al menos una placa.',
            'placas.*.placa.required' => 'El número de placa es obligatorio.',
            'placas.*.placa.regex' => 'El formato de placa no es válido para :attribute.',
            'placas.*.monto_calculado.required' => 'El monto calculado es obligatorio.',
            'placas.*.monto_calculado.min' => 'El monto calculado debe ser mayor a 0.',
            'placas.*.fecha_inicio.required' => 'La fecha de inicio de la placa es obligatoria.',
            'placas.*.fecha_inicio.date_format' => 'La fecha de inicio debe tener el formato DD-MM-YYYY.',
            'placas.*.fecha_fin.required' => 'La fecha de fin de la placa es obligatoria.',
            'placas.*.fecha_fin.date_format' => 'La fecha de fin debe tener el formato DD-MM-YYYY.',
            'placas.*.fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }

    public function mount(): void
    {
        $this->fecha_inicio_periodo = now()->startOfMonth()->format('d-m-Y');
        $this->fecha_fin_periodo = now()->endOfMonth()->format('d-m-Y');
        $this->placas = collect([]);
    }

    public function render()
    {
        return view('livewire.cobros.form');
    }

    #[On('openCreateForm')]
    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isOpen = true;
    }

    #[On('openEditForm')]
    public function openEdit(Cobro $cobro): void
    {
        $this->resetForm();
        $this->cobro = $cobro;
        $this->isEditing = true;
        $this->fillForm();
        $this->isOpen = true;
    }

    private function resetForm(): void
    {
        $this->cobro = null;
        $this->cliente_id = null;
        $this->servicio_id = null;
        $this->descripcion_servicio_personalizado = '';
        $this->monto_base = 0.0;
        $this->cantidad_placas = 1;
        $this->monto_unitario = 0.0;
        $this->periodo_facturacion = 'Mensual';
        $this->moneda = 'PEN';
        $this->fecha_inicio_periodo = now()->startOfMonth()->format('d-m-Y');
        $this->fecha_fin_periodo = now()->endOfMonth()->format('d-m-Y');
        $this->dias_para_vencimiento = 30;
        $this->estado = 'activo';
        $this->notas = '';
        $this->placas = collect(); // Inicializar como colección vacía
        $this->nueva_placa = '';
        $this->monto_total_calculado = 0.0;

        $this->actualizarCantidadPlacas();
        $this->resetValidation();
    }

    private function fillForm(): void
    {
        if ($this->cobro) {
            $this->cliente_id = $this->cobro->cliente_id;
            $this->servicio_id = $this->cobro->servicio_id;
            $this->descripcion_servicio_personalizado = $this->cobro->descripcion_servicio_personalizado ?? '';
            $this->monto_base = (float) $this->cobro->monto_base;
            $this->cantidad_placas = $this->cobro->cantidad_placas;
            $this->monto_unitario = (float) $this->cobro->monto_unitario;
            $this->periodo_facturacion = $this->cobro->periodo_facturacion;
            $this->moneda = $this->cobro->moneda;
            $this->fecha_inicio_periodo = $this->cobro->fecha_inicio_periodo->format('d-m-Y');
            $this->fecha_fin_periodo = $this->cobro->fecha_fin_periodo->format('d-m-Y');
            $this->dias_para_vencimiento = $this->cobro->dias_para_vencimiento;
            $this->estado = $this->cobro->estado;
            $this->notas = $this->cobro->notas ?? '';

            // Cargar las placas existentes
            $this->placas = collect($this->cobro->cobroPlacas->map(function ($cobroPlaca) {
                return [
                    'id' => $cobroPlaca->id,
                    'placa' => $cobroPlaca->placa,
                    'monto_calculado' => (float) $this->monto_base, // Usar monto fijo
                    'fecha_inicio' => $cobroPlaca->fecha_inicio ? $cobroPlaca->fecha_inicio->format('d-m-Y') : '',
                    'fecha_fin' => $cobroPlaca->fecha_fin ? $cobroPlaca->fecha_fin->format('d-m-Y') : '',
                ];
            }));

            $this->actualizarCantidadPlacas();
            $this->calcularMontoTotal();
        }
    }

    public function updatedServicioId(): void
    {
        if ($this->servicio_id) {
            $servicio = Servicio::find($this->servicio_id);
            if ($servicio) {
                $this->monto_base = (float) $servicio->precio_base;
                $this->monto_unitario = (float) $servicio->precio_base;
                $this->calcularMontoTotal();
            }
        }
    }

    public function updatedMontoBase(): void
    {
        $this->calcularMontoTotal();
    }

    public function updatedFechaInicioPeriodo(): void
    {
        // Solo actualizar si hay placas reales
        if ($this->tieneplacasReales()) {
            $this->placas = $this->placas->map(function ($placa) {
                if (isset($placa['placa']) && ! empty($placa['placa'])) {
                    $placa['fecha_inicio'] = $this->fecha_inicio_periodo;
                }

                return $placa;
            });
        }

        $this->actualizarDiasParaVencimiento();
        $this->calcularMontoTotal();
    }

    public function updatedFechaFinPeriodo(): void
    {
        // Solo actualizar si hay placas reales
        if ($this->tieneplacasReales()) {
            $this->placas = $this->placas->map(function ($placa) {
                if (isset($placa['placa']) && ! empty($placa['placa'])) {
                    $placa['fecha_fin'] = $this->fecha_fin_periodo;
                }

                return $placa;
            });
        }

        $this->actualizarDiasParaVencimiento();
        $this->calcularMontoTotal();
    }

    public function updatedPeriodoFacturacion(): void
    {
        // Calcular fechas según el período seleccionado
        $this->calcularFechasPorPeriodo();

        // Actualizar días para vencimiento
        $this->actualizarDiasParaVencimiento();

        // Recalcular montos
        $this->calcularMontoTotal();
    }

    private function calcularFechasPorPeriodo(): void
    {
        if ($this->fecha_inicio_periodo) {
            $inicio = Carbon::createFromFormat('d-m-Y', $this->fecha_inicio_periodo);

            $fechaFin = match ($this->periodo_facturacion) {
                'Mensual' => $inicio->copy()->endOfMonth(),
                'Bimensual' => $inicio->copy()->addMonths(2)->endOfMonth(),
                'Trimestral' => $inicio->copy()->addMonths(3)->endOfMonth(),
                'Semestral' => $inicio->copy()->addMonths(6)->endOfMonth(),
                'Anual' => $inicio->copy()->addYear()->subDay(),
                default => $inicio->copy()->endOfMonth()
            };

            $this->fecha_fin_periodo = $fechaFin->format('d-m-Y');

            // Solo actualizar fechas de placas si hay placas reales
            if ($this->tieneplacasReales()) {
                $this->placas = $this->placas->map(function ($placa) {
                    if (isset($placa['placa']) && ! empty($placa['placa'])) {
                        $placa['fecha_inicio'] = $this->fecha_inicio_periodo;
                        $placa['fecha_fin'] = $this->fecha_fin_periodo;
                    }

                    return $placa;
                });
            }
        }
    }

    public function actualizarDiasParaVencimiento(): void
    {
        if ($this->fecha_inicio_periodo && $this->fecha_fin_periodo) {
            try {
                $inicio = Carbon::createFromFormat('d-m-Y', $this->fecha_inicio_periodo);
                $fin = Carbon::createFromFormat('d-m-Y', $this->fecha_fin_periodo);

                // Calcular días del período (incluye inicio y fin)
                $diasPeriodo = $inicio->diffInDays($fin) + 1;

                // Usar un mínimo de 30 días para vencimiento, pero adaptar según el período
                $diasVencimiento = match ($this->periodo_facturacion) {
                    'Mensual' => max(30, $diasPeriodo),
                    'Bimensual' => max(45, intval($diasPeriodo * 0.5)),
                    'Trimestral' => max(60, intval($diasPeriodo * 0.3)),
                    'Semestral' => max(90, intval($diasPeriodo * 0.25)),
                    'Anual' => max(120, intval($diasPeriodo * 0.2)),
                    default => max(30, $diasPeriodo)
                };

                $this->dias_para_vencimiento = min($diasVencimiento, 365); // No exceder 365 días

            } catch (\Exception $e) {
                // Si hay error en fechas, usar valor por defecto según período
                $this->dias_para_vencimiento = match ($this->periodo_facturacion) {
                    'Mensual' => 30,
                    'Bimensual' => 45,
                    'Trimestral' => 60,
                    'Semestral' => 90,
                    'Anual' => 120,
                    default => 30
                };
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        // Validación adicional de fechas de placas
        if (! $this->validarFechasPlacas()) {
            $this->notification()->error(
                'Error de validación',
                'Por favor revisa las fechas de las placas. Deben estar dentro del período del cobro.'
            );

            return;
        }

        try {
            $data = [
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'descripcion_servicio_personalizado' => $this->descripcion_servicio_personalizado,
                'monto_base' => $this->monto_base,
                'cantidad_placas' => $this->tieneplacasReales() ? $this->placas->count() : $this->cantidad_placas,
                'monto_unitario' => $this->monto_unitario,
                'periodo_facturacion' => $this->periodo_facturacion,
                'moneda' => $this->moneda,
                'fecha_inicio_periodo' => Carbon::createFromFormat('d-m-Y', $this->fecha_inicio_periodo)->format('Y-m-d'),
                'fecha_fin_periodo' => Carbon::createFromFormat('d-m-Y', $this->fecha_fin_periodo)->format('Y-m-d'),
                'dias_para_vencimiento' => $this->dias_para_vencimiento,
                'estado' => $this->estado,
                'notas' => $this->notas,
            ];

            if ($this->isEditing && $this->cobro) {
                $this->cobro->update($data);

                // Actualizar placas
                $this->actualizarPlacas($this->cobro);

                // Recargar las placas desde la base de datos para mantener consistencia
                $this->cobro->refresh();
                // $this->cargarPlacasDesdeBaseDatos();

                $this->notification()->success(
                    'Cobro actualizado',
                    'El cobro ha sido actualizado correctamente.'
                );
            } else {
                $cobro = Cobro::create($data);

                // Crear placas
                $this->crearPlacas($cobro);

                // Recargar las placas desde la base de datos
                $cobro->refresh();
                $this->cobro = $cobro;
                // $this->cargarPlacasDesdeBaseDatos();

                $this->notification()->success(
                    'Cobro creado',
                    'El cobro ha sido creado correctamente.'
                );
            }

            $this->closeModal();
            $this->dispatch('cobrosSaved');
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error',
                'Ha ocurrido un error al guardar el cobro: '.$e->getMessage()
            );
        }
    }

    private function crearPlacas(Cobro $cobro): void
    {
        if ($this->placas->count() > 0) {
            foreach ($this->placas as $placaData) {
                $cobro->cobroPlacas()->create([
                    'placa' => $placaData['placa'],
                    'monto_calculado' => $this->monto_base, // Usar monto fijo
                    'fecha_inicio' => isset($placaData['fecha_inicio']) && $placaData['fecha_inicio'] ? Carbon::createFromFormat('d-m-Y', $placaData['fecha_inicio'])->format('Y-m-d') : null,
                    'fecha_fin' => isset($placaData['fecha_fin']) && $placaData['fecha_fin'] ? Carbon::createFromFormat('d-m-Y', $placaData['fecha_fin'])->format('Y-m-d') : null,
                ]);
            }
        }
    }

    private function actualizarPlacas(Cobro $cobro): void
    {
        // Eliminar placas que ya no están en la lista
        $placasActuales = $this->placas->pluck('id')->filter()->toArray();
        $cobro->cobroPlacas()->whereNotIn('id', $placasActuales)->delete();

        // Actualizar o crear placas
        foreach ($this->placas as $placaData) {
            if (isset($placaData['id']) && $placaData['id']) {
                // Actualizar placa existente
                $cobro->cobroPlacas()->where('id', $placaData['id'])->update([
                    'placa' => $placaData['placa'],
                    'monto_calculado' => $this->monto_base, // Usar monto fijo
                    'fecha_inicio' => isset($placaData['fecha_inicio']) && $placaData['fecha_inicio'] ? Carbon::createFromFormat('d-m-Y', $placaData['fecha_inicio'])->format('Y-m-d') : null,
                    'fecha_fin' => isset($placaData['fecha_fin']) && $placaData['fecha_fin'] ? Carbon::createFromFormat('d-m-Y', $placaData['fecha_fin'])->format('Y-m-d') : null,
                ]);
            } else {
                // Crear nueva placa
                $cobro->cobroPlacas()->create([
                    'placa' => $placaData['placa'],
                    'monto_calculado' => $this->monto_base, // Usar monto fijo
                    'fecha_inicio' => isset($placaData['fecha_inicio']) && $placaData['fecha_inicio'] ? Carbon::createFromFormat('d-m-Y', $placaData['fecha_inicio'])->format('Y-m-d') : null,
                    'fecha_fin' => isset($placaData['fecha_fin']) && $placaData['fecha_fin'] ? Carbon::createFromFormat('d-m-Y', $placaData['fecha_fin'])->format('Y-m-d') : null,
                ]);
            }
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetForm();

        // Forzar el refreso del componente padre
        $this->dispatch('modal-closed');
    }

    /**
     * Carga las placas desde la base de datos
     */
    private function cargarPlacasDesdeBaseDatos(): void
    {
        if ($this->cobro) {
            $this->placas = collect($this->cobro->cobroPlacas->map(function ($cobroPlaca) {
                return [
                    'id' => $cobroPlaca->id,
                    'placa' => $cobroPlaca->placa,
                    'monto_calculado' => (float) $this->monto_base, // Usar monto fijo
                    'fecha_inicio' => $cobroPlaca->fecha_inicio ? $cobroPlaca->fecha_inicio->format('d-m-Y') : '',
                    'fecha_fin' => $cobroPlaca->fecha_fin ? $cobroPlaca->fecha_fin->format('d-m-Y') : '',
                ];
            }));

            // Normalizar estructura del array de placas usando métodos de Collection
            $this->placas = $this->placas->map(function ($placa, $index) {
                if (is_array($placa)) {
                    return array_merge([
                        'id' => null,
                        'placa' => '',
                        'monto_calculado' => 0.0,
                        'fecha_inicio' => '',
                        'fecha_fin' => '',
                    ], $placa);
                }

                return $placa;
            });

            $this->actualizarCantidadPlacas();
        }
    }

    // Métodos para gestión de placas
    public function agregarPlaca(): void
    {
        if (empty($this->nueva_placa)) {
            $this->notification()->error(
                'Campo requerido',
                'Por favor ingresa el número de placa.'
            );

            return;
        }

        // Verificar que la placa no esté duplicada
        $placaExistente = $this->placas->first(function ($placa) {
            return isset($placa['placa']) && strtoupper($placa['placa']) === strtoupper($this->nueva_placa);
        });

        if ($placaExistente) {
            $this->addError('nueva_placa', 'Esta placa ya está agregada.');
            $this->notification()->error(
                'Placa duplicada',
                'Esta placa ya está agregada a la lista.'
            );

            return;
        }

        $montoBase = (float) ($this->monto_base ?: 0);

        // Agregar placa con estructura completa y correcta
        $this->placas->push([
            'id' => null,
            'placa' => strtoupper($this->nueva_placa),
            'fecha_inicio' => $this->fecha_inicio_periodo,
            'fecha_fin' => $this->fecha_fin_periodo,
            'monto_calculado' => $montoBase,
        ]);

        $this->nueva_placa = '';
        $this->limpiarColeccionPlacas();
        $this->actualizarCantidadPlacas();
        $this->calcularMontoTotal();
        $this->resetErrorBag('nueva_placa');

        $this->notification()->success(
            'Placa agregada',
            'La placa ha sido agregada correctamente.'
        );
    }

    public function removerPlaca(int $index): void
    {
        $this->placas = $this->placas->forget($index)->values();
        $this->limpiarColeccionPlacas();
        $this->actualizarCantidadPlacas();
        $this->calcularMontoTotal();
    }

    /**
     * Limpia la colección eliminando elementos inválidos o vacíos
     */
    private function limpiarColeccionPlacas(): void
    {
        $this->placas = $this->placas->filter(function ($placa) {
            return isset($placa['placa']) && ! empty($placa['placa']);
        })->values();
    }

    /**
     * Verifica si hay placas reales válidas en la colección
     */
    private function tieneplacasReales(): bool
    {
        return $this->placas->filter(function ($placa) {
            return isset($placa['placa']) && ! empty($placa['placa']);
        })->count() > 0;
    }

    private function calcularMontoTotal(): void
    {
        $total = 0;
        $montoBase = $this->monto_base;

        if ($this->tieneplacasReales()) {
            // Si hay placas específicas reales, usar monto fijo por placa
            $this->placas = $this->placas->map(function ($placa) use (&$total, $montoBase) {
                // Solo procesar placas válidas
                if (isset($placa['placa']) && ! empty($placa['placa'])) {
                    $placa['monto_calculado'] = $montoBase;
                    $total += $montoBase;
                }

                return $placa;
            });
        } else {
            // Si no hay placas específicas, usar la cantidad de placas genérica
            $total = $montoBase * $this->cantidad_placas;
        }

        $this->monto_total_calculado = round($total, 2);
    }

    private function actualizarCantidadPlacas(): void
    {
        // Solo contar placas que tienen un número de placa válido
        $placasReales = $this->placas->filter(function ($placa) {
            return isset($placa['placa']) && ! empty($placa['placa']);
        })->count();

        $this->cantidad_placas = max(1, $placasReales);
    }

    public function updatedPlacas(): void
    {
        $this->limpiarColeccionPlacas();
        $this->actualizarCantidadPlacas();
        $this->calcularMontoTotal();
    }

    /**
     * Actualiza las fechas de una placa
     */
    public function actualizarFechasPlaca(int $index): void
    {
        if ($this->placas->has($index)) {
            // Limpiar errores previos para esta placa
            $this->resetErrorBag("placas.{$index}.fecha_inicio");
            $this->resetErrorBag("placas.{$index}.fecha_fin");

            // Validar fechas de esta placa específica
            $this->validarFechaPlacaEspecifica($index);

            // Recalcular montos
            $this->calcularMontoTotal();
        }
    }

    /**
     * Actualiza un campo específico de una placa
     */
    private function actualizarPlacaEnIndice(int $index, string $campo, $valor): void
    {
        if ($this->placas->has($index)) {
            $this->placas = $this->placas->map(function ($placa, $i) use ($index, $campo, $valor) {
                if ($i === $index) {
                    $placa[$campo] = $valor;
                }

                return $placa;
            });
        }
    }

    /**
     * Valida las fechas de una placa específica
     */
    private function validarFechaPlacaEspecifica(int $index): void
    {
        if (! $this->placas->has($index)) {
            return;
        }

        $placa = $this->placas[$index];

        if (empty($this->fecha_inicio_periodo) || empty($this->fecha_fin_periodo)) {
            return;
        }

        try {
            $inicioPeriodo = Carbon::createFromFormat('d-m-Y', $this->fecha_inicio_periodo);
            $finPeriodo = Carbon::createFromFormat('d-m-Y', $this->fecha_fin_periodo);

            // Validar fecha de inicio
            if (isset($placa['fecha_inicio']) && ! empty($placa['fecha_inicio'])) {
                $fechaInicioPlaca = Carbon::createFromFormat('d-m-Y', $placa['fecha_inicio']);
                if ($fechaInicioPlaca->lt($inicioPeriodo) || $fechaInicioPlaca->gt($finPeriodo)) {
                    $this->addError(
                        "placas.{$index}.fecha_inicio",
                        'Debe estar entre '.$inicioPeriodo->format('d/m/Y').' y '.$finPeriodo->format('d/m/Y')
                    );
                }
            }

            // Validar fecha de fin
            if (isset($placa['fecha_fin']) && ! empty($placa['fecha_fin'])) {
                $fechaFinPlaca = Carbon::createFromFormat('d-m-Y', $placa['fecha_fin']);
                if ($fechaFinPlaca->lt($inicioPeriodo) || $fechaFinPlaca->gt($finPeriodo)) {
                    $this->addError(
                        "placas.{$index}.fecha_fin",
                        'Debe estar entre '.$inicioPeriodo->format('d/m/Y').' y '.$finPeriodo->format('d/m/Y')
                    );
                }

                // Validar que fecha_fin >= fecha_inicio
                if (isset($placa['fecha_inicio']) && ! empty($placa['fecha_inicio'])) {
                    $fechaInicioPlaca = Carbon::createFromFormat('d-m-Y', $placa['fecha_inicio']);
                    if ($fechaFinPlaca->lt($fechaInicioPlaca)) {
                        $this->addError(
                            "placas.{$index}.fecha_fin",
                            'Debe ser posterior o igual a la fecha de inicio'
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            // Error de formato de fecha
        }
    }

    /**
     * Valida que las fechas de las placas estén dentro del rango del período
     */
    private function validarFechasPlacas(): bool
    {
        if (empty($this->fecha_inicio_periodo) || empty($this->fecha_fin_periodo)) {
            $this->addError('fecha_inicio_periodo', 'Las fechas del período son requeridas.');

            return false;
        }

        try {
            $inicioPeriodo = Carbon::createFromFormat('d-m-Y', $this->fecha_inicio_periodo);
            $finPeriodo = Carbon::createFromFormat('d-m-Y', $this->fecha_fin_periodo);
        } catch (\Exception $e) {
            $this->addError('fecha_inicio_periodo', 'Formato de fecha inválido.');

            return false;
        }

        // Validar que la fecha de fin sea posterior a la de inicio
        if ($finPeriodo->lt($inicioPeriodo)) {
            $this->addError('fecha_fin_periodo', 'La fecha de fin debe ser posterior a la fecha de inicio.');

            return false;
        }

        $hasErrors = false;

        foreach ($this->placas as $index => $placa) {
            // Validar fecha de inicio de la placa
            if (isset($placa['fecha_inicio']) && ! empty($placa['fecha_inicio'])) {
                try {
                    $fechaInicioPlaca = Carbon::createFromFormat('d-m-Y', $placa['fecha_inicio']);
                    if ($fechaInicioPlaca->lt($inicioPeriodo) || $fechaInicioPlaca->gt($finPeriodo)) {
                        $this->addError(
                            "placas.{$index}.fecha_inicio",
                            'La fecha de inicio debe estar dentro del período del cobro ('.
                                $inicioPeriodo->format('d/m/Y').' - '.$finPeriodo->format('d/m/Y').').'
                        );
                        $hasErrors = true;
                    }
                } catch (\Exception $e) {
                    $this->addError(
                        "placas.{$index}.fecha_inicio",
                        'Formato de fecha inválido.'
                    );
                    $hasErrors = true;
                }
            }

            // Validar fecha de fin de la placa
            if (isset($placa['fecha_fin']) && ! empty($placa['fecha_fin'])) {
                try {
                    $fechaFinPlaca = Carbon::createFromFormat('d-m-Y', $placa['fecha_fin']);
                    if ($fechaFinPlaca->lt($inicioPeriodo) || $fechaFinPlaca->gt($finPeriodo)) {
                        $this->addError(
                            "placas.{$index}.fecha_fin",
                            'La fecha de fin debe estar dentro del período del cobro ('.
                                $inicioPeriodo->format('d/m/Y').' - '.$finPeriodo->format('d/m/Y').').'
                        );
                        $hasErrors = true;
                    }

                    // Validar que fecha_fin >= fecha_inicio de la placa
                    if (isset($placa['fecha_inicio']) && ! empty($placa['fecha_inicio'])) {
                        $fechaInicioPlaca = Carbon::createFromFormat('d-m-Y', $placa['fecha_inicio']);
                        if ($fechaFinPlaca->lt($fechaInicioPlaca)) {
                            $this->addError(
                                "placas.{$index}.fecha_fin",
                                'La fecha de fin debe ser posterior o igual a la fecha de inicio de la placa.'
                            );
                            $hasErrors = true;
                        }
                    }
                } catch (\Exception $e) {
                    $this->addError(
                        "placas.{$index}.fecha_fin",
                        'Formato de fecha inválido.'
                    );
                    $hasErrors = true;
                }
            }
        }

        return ! $hasErrors;
    }
}
