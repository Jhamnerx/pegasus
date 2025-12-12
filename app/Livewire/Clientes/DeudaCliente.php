<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use App\Models\PlantillaMensaje;
use App\Services\WhatsAppService;
use Livewire\Attributes\On;
use Livewire\Component;
use WireUi\Traits\WireUiActions;

class DeudaCliente extends Component
{
    use WireUiActions;

    public bool $isOpen = false;

    public ?Cliente $cliente = null;

    public array $recibos = [];

    public float $totalDeuda = 0;

    public string $mensajePersonalizado = '';

    public bool $usarPlantilla = true;

    public function render()
    {
        return view('livewire.clientes.deuda-cliente');
    }

    #[On('openDeudaModal')]
    public function openModal(Cliente $cliente): void
    {
        $this->cliente = $cliente;
        $this->cargarDeudas();
        $this->cargarMensajePlantilla();
        $this->isOpen = true;
    }

    private function cargarDeudas(): void
    {
        if ($this->cliente) {
            $this->recibos = $this->cliente->recibosNoPagados()->get()->toArray();
            $this->totalDeuda = $this->cliente->total_deuda;
        }
    }

    private function cargarMensajePlantilla(): void
    {
        $plantilla = PlantillaMensaje::porTipo('recordatorio_pago');

        if ($plantilla && $this->cliente && count($this->recibos) > 0) {
            // Construir lista de recibos
            $listaRecibos = collect($this->recibos)->map(function ($recibo) {
                $numero = str_pad($recibo['numero_recibo'], 8, '0', STR_PAD_LEFT);
                $monto = 'S/ '.number_format($recibo['monto_recibo'], 2);
                $vencimiento = \Carbon\Carbon::parse($recibo['fecha_vencimiento'])->format('d/m/Y');

                return "• Recibo {$numero} - {$monto} - Vence: {$vencimiento}";
            })->implode("\n");

            $variables = [
                'cliente_nombre' => $this->cliente->nombre_cliente,
                'total_deuda' => 'S/ '.number_format($this->totalDeuda, 2),
                'cantidad_recibos' => count($this->recibos),
                'lista_recibos' => $listaRecibos,
                'empresa_nombre' => config('app.name', 'Pegasus'),
            ];

            // Crear mensaje básico
            $this->mensajePersonalizado = "Estimado(a) *{$variables['cliente_nombre']}*,\n\n";
            $this->mensajePersonalizado .= "Le recordamos que tiene *{$variables['cantidad_recibos']}* recibo(s) pendiente(s) de pago:\n\n";
            $this->mensajePersonalizado .= "{$variables['lista_recibos']}\n\n";
            $this->mensajePersonalizado .= "💰 *Total a pagar: {$variables['total_deuda']}*\n\n";
            $this->mensajePersonalizado .= "Por favor, regularice su situación a la brevedad posible.\n\n";
            $this->mensajePersonalizado .= "Gracias,\n_{$variables['empresa_nombre']}_";
        }
    }

    public function enviarRecordatorio(): void
    {
        if (! $this->cliente) {
            $this->notification()->error('Error', 'No se ha seleccionado un cliente.');

            return;
        }

        if (! $this->cliente->tieneTelefono()) {
            $this->notification()->error(
                'Sin teléfono',
                'El cliente no tiene un número de teléfono registrado.'
            );

            return;
        }

        if (empty($this->mensajePersonalizado)) {
            $this->notification()->error('Error', 'Debe escribir un mensaje.');

            return;
        }

        $whatsappService = app(WhatsAppService::class);
        $telefonoPrincipal = $this->cliente->telefono_principal;

        $enviado = $whatsappService->sendMessage(
            $telefonoPrincipal,
            $this->mensajePersonalizado
        );

        if ($enviado) {
            $this->notification()->success(
                'Mensaje enviado',
                "Se ha enviado el recordatorio de pago a {$this->cliente->nombre_cliente}"
            );
            $this->closeModal();
        } else {
            $this->notification()->error(
                'Error al enviar',
                'No se pudo enviar el mensaje de WhatsApp. Revise los logs para más detalles.'
            );
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->cliente = null;
        $this->recibos = [];
        $this->totalDeuda = 0;
        $this->mensajePersonalizado = '';
        $this->usarPlantilla = true;
    }
}
