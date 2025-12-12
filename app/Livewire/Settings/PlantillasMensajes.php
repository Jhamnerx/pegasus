<?php

namespace App\Livewire\Settings;

use App\Models\PlantillaMensaje;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class PlantillasMensajes extends Component
{
    use WireUiActions;

    public $plantillas = [];

    public $plantillaSeleccionada = null;

    public $editando = false;

    // Campos del formulario
    public string $mensaje = '';

    public function mount(): void
    {
        $this->cargarPlantillas();
    }

    public function render()
    {
        return view('livewire.settings.plantillas-mensajes');
    }

    public function cargarPlantillas(): void
    {
        $this->plantillas = PlantillaMensaje::orderBy('tipo')->get();
    }

    public function seleccionarPlantilla($plantillaId): void
    {
        $this->plantillaSeleccionada = PlantillaMensaje::find($plantillaId);

        if ($this->plantillaSeleccionada) {
            $this->mensaje = $this->plantillaSeleccionada->mensaje;
            $this->editando = true;
        }
    }

    public function guardarPlantilla(): void
    {
        $this->validate([
            'mensaje' => 'required|string',
        ]);

        if ($this->plantillaSeleccionada) {
            // Actualizar solo el mensaje de la plantilla existente
            $this->plantillaSeleccionada->update([
                'mensaje' => $this->mensaje,
            ]);
            $this->notification()->success('Plantilla actualizada correctamente');
        }

        $this->cargarPlantillas();
        $this->cancelarEdicion();
    }

    public function cancelarEdicion(): void
    {
        $this->resetearFormulario();
        $this->editando = false;
        $this->plantillaSeleccionada = null;
    }

    public function resetearFormulario(): void
    {
        $this->mensaje = '';
    }

    public function getVariablesDisponibles(): array
    {
        if (! $this->plantillaSeleccionada) {
            return [];
        }

        return match ($this->plantillaSeleccionada->tipo) {
            'creacion_recibo' => [
                'cliente_nombre' => 'Nombre del cliente',
                'numero_recibo' => 'Número del recibo',
                'servicio_nombre' => 'Nombre del servicio',
                'monto_recibo' => 'Monto del recibo',
                'fecha_vencimiento' => 'Fecha de vencimiento',
                'placas_recibo' => 'Placas incluidas en el recibo',
                'url_publica' => 'URL pública del recibo',
                'empresa_nombre' => 'Nombre de la empresa',
            ],
            'recordatorio_pago' => [
                'cliente_nombre' => 'Nombre del cliente',
                'numero_recibo' => 'Número del recibo',
                'placas_recibo' => 'Placas incluidas en el recibo',
                'servicio_nombre' => 'Nombre del servicio',
                'monto_recibo' => 'Monto del recibo',
                'dias_texto' => 'Días para/desde vencimiento',
                'periodo_facturacion' => 'Período de facturación',
                'empresa_nombre' => 'Nombre de la empresa',
            ],
            default => []
        };
    }

    public function insertarVariable($variable): void
    {
        $this->mensaje .= '{'.$variable.'}';
    }
}
