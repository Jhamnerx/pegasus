<?php

namespace App\Livewire\Settings;

use App\Models\Configuracion;
use App\Services\WhatsAppService;
use Livewire\Component;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;

class Empresa extends Component
{
    use WireUiActions;
    use WithFileUploads;

    public string $nombre = '';

    public string $direccion = '';

    public string $telefono = '';

    public string $correo = '';

    public string $moneda = '';

    public $logo;

    public string $logoUrl = '';

    public array $metodosPago = [];

    public string $nuevoMetodoPago = '';

    public bool $isOpenModalMetodo = false;

    public ?int $metodoPagoEditandoIndex = null;

    // Propiedades para prueba de WhatsApp
    public bool $isOpenModalWhatsapp = false;

    public string $numeroWhatsapp = '';

    public string $mensajeWhatsapp = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->cargarConfiguraciones();
    }

    public function render()
    {
        return view('livewire.settings.empresa');
    }

    /**
     * Cargar las configuraciones desde la base de datos
     */
    public function cargarConfiguraciones(): void
    {
        $configuracion = Configuracion::obtenerEmpresa();

        if ($configuracion) {
            $this->nombre = $configuracion->razon_social ?? '';
            $this->direccion = $configuracion->direccion ?? '';
            $this->telefono = $configuracion->telefono ?? '';
            $this->correo = $configuracion->email ?? '';
            $this->moneda = $configuracion->moneda ?? 'PEN';
            $this->logoUrl = $configuracion->logo ?? '';
            $this->metodosPago = is_string($configuracion->metodos_pago)
                ? json_decode($configuracion->metodos_pago, true) ?? []
                : $configuracion->metodos_pago ?? [];
        }
    }

    /**
     * Actualizar las configuraciones de la empresa
     */
    public function actualizarConfiguraciones(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:500'],
            'telefono' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:255'],
            'moneda' => ['required', 'string', 'in:PEN,USD,EUR'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $datos = [
            'razon_social' => $this->nombre,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->correo,
            'moneda' => $this->moneda,
            'metodos_pago' => json_encode($this->metodosPago),
        ];

        // Si se subió un nuevo logo, procesarlo
        if ($this->logo) {
            $logoBase64 = $this->procesarLogo();
            $datos['logo'] = $logoBase64;
            $this->logoUrl = $logoBase64;
        }

        // Actualizar configuraciones usando el modelo
        Configuracion::actualizarEmpresa($datos);

        $this->dispatch('empresa-updated');

        $this->notification()->success('Configuraciones de empresa actualizadas', 'Las configuraciones de la empresa han sido actualizadas correctamente.');
    }

    /**
     * Procesar el logo subido y convertirlo a base64
     */
    private function procesarLogo(): string
    {
        $logoContent = file_get_contents($this->logo->getRealPath());
        $mimeType = $this->logo->getMimeType();

        return 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
    }

    /**
     * Limpiar el logo seleccionado
     */
    public function limpiarLogo(): void
    {
        $this->logo = null;
    }

    /**
     * Eliminar el logo actual
     */
    public function eliminarLogo(): void
    {
        $configuracion = Configuracion::obtenerEmpresa();
        if ($configuracion) {
            $configuracion->update(['logo' => '']);
        }

        $this->logoUrl = '';
        $this->dispatch('empresa-updated');
        $this->notification()->success('Logo eliminado correctamente.');
    }

    /**
     * Abrir modal para agregar método de pago
     */
    public function abrirModalMetodo(): void
    {
        $this->nuevoMetodoPago = '';
        $this->metodoPagoEditandoIndex = null;
        $this->isOpenModalMetodo = true;
    }

    /**
     * Abrir modal para editar método de pago
     */
    public function editarMetodoPago(int $index): void
    {
        $this->nuevoMetodoPago = $this->metodosPago[$index];
        $this->metodoPagoEditandoIndex = $index;
        $this->isOpenModalMetodo = true;
    }

    /**
     * Guardar método de pago (nuevo o editado)
     */
    public function guardarMetodoPago(): void
    {
        $this->validate([
            'nuevoMetodoPago' => ['required', 'string', 'max:100'],
        ]);

        if ($this->metodoPagoEditandoIndex !== null) {
            // Editar método existente
            $this->metodosPago[$this->metodoPagoEditandoIndex] = $this->nuevoMetodoPago;
        } else {
            // Agregar nuevo método
            if (! in_array($this->nuevoMetodoPago, $this->metodosPago)) {
                $this->metodosPago[] = $this->nuevoMetodoPago;
            }
        }

        $this->cerrarModalMetodo();
        $this->notification()->success('Método de pago guardado correctamente.');
    }

    /**
     * Eliminar método de pago
     */
    public function eliminarMetodoPago(int $index): void
    {
        if (isset($this->metodosPago[$index])) {
            unset($this->metodosPago[$index]);
            $this->metodosPago = array_values($this->metodosPago); // Reindexar array
            $this->notification()->success('Método de pago eliminado correctamente.');
        }
    }

    /**
     * Cerrar modal de método de pago
     */
    public function cerrarModalMetodo(): void
    {
        $this->isOpenModalMetodo = false;
        $this->nuevoMetodoPago = '';
        $this->metodoPagoEditandoIndex = null;
    }

    /**
     * Abrir modal para prueba de WhatsApp
     */
    public function abrirModalWhatsapp(): void
    {
        $this->numeroWhatsapp = '';
        $this->mensajeWhatsapp = 'Mensaje de prueba desde PEGASUS';
        $this->isOpenModalWhatsapp = true;
    }

    /**
     * Enviar mensaje de prueba por WhatsApp
     */
    public function enviarPruebaWhatsapp(): void
    {
        $this->validate([
            'numeroWhatsapp' => ['required', 'string', 'min:9', 'max:15'],
            'mensajeWhatsapp' => ['required', 'string', 'max:500'],
        ]);

        try {
            $whatsappService = new WhatsAppService;

            if (! $whatsappService->isConfigured()) {
                $this->notification()->error(
                    'Configuración incompleta',
                    'El servicio de WhatsApp no está configurado correctamente. Verifique las credenciales en el archivo de configuración.'
                );

                return;
            }

            $enviado = $whatsappService->sendMessage($this->numeroWhatsapp, $this->mensajeWhatsapp);

            if ($enviado) {
                $this->notification()->success(
                    'Mensaje enviado',
                    'El mensaje de prueba se envió correctamente a ' . $this->numeroWhatsapp
                );
                $this->cerrarModalWhatsapp();
            } else {
                $this->notification()->error(
                    'Error al enviar',
                    'No se pudo enviar el mensaje. Verifique el número y la configuración del servicio.'
                );
            }
        } catch (\Exception $e) {
            $this->notification()->error(
                'Error inesperado',
                'Ocurrió un error al intentar enviar el mensaje: ' . $e->getMessage()
            );
        }
    }

    /**
     * Cerrar modal de prueba de WhatsApp
     */
    public function cerrarModalWhatsapp(): void
    {
        $this->isOpenModalWhatsapp = false;
        $this->numeroWhatsapp = '';
        $this->mensajeWhatsapp = '';
    }
}
