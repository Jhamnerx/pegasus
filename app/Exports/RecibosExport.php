<?php

namespace App\Exports;

use App\Models\Recibo;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RecibosExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $clienteId;

    protected $estado;

    protected $fechaDesde;

    protected $fechaHasta;

    public function __construct($clienteId = null, $estado = 'todos', $fechaDesde = null, $fechaHasta = null)
    {
        $this->clienteId = $clienteId;
        $this->estado = $estado;
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query = Recibo::query()->with(['detalles']);

        // Aplicar filtros
        if ($this->clienteId) {
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.id')) = ?", [$this->clienteId]);
        }

        if ($this->estado !== 'todos') {
            if ($this->estado === 'vencidos') {
                $query->where('fecha_vencimiento', '<', now())
                    ->where('estado_recibo', '!=', 'pagado');
            } else {
                $query->where('estado_recibo', $this->estado);
            }
        }

        if ($this->fechaDesde) {
            $query->where('fecha_emision', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->where('fecha_emision', '<=', $this->fechaHasta);
        }

        return $query->orderBy('fecha_emision', 'desc');
    }

    public function headings(): array
    {
        return [
            'Número',
            'Cliente',
            'RUC/DNI',
            'Placa',
            'Servicio',
            'Monto',
            'Fecha Emisión',
            'Fecha Vencimiento',
            'Estado',
            'Método Pago',
            'Fecha Pago',
            'Observaciones',
        ];
    }

    /**
     * @param  mixed  $recibo
     */
    public function map($recibo): array
    {
        // Obtener placas desde los detalles del recibo
        $placas = $recibo->detalles->pluck('placa')->filter()->unique()->implode(', ');

        // Si no hay placas en detalles, usar el JSON como fallback
        if (empty($placas)) {
            $placas = $recibo->data_cobro['placa'] ?? 'N/A';
        }

        return [
            $recibo->numero_recibo,
            $recibo->data_cliente['nombre_cliente'] ?? 'N/A',
            $recibo->data_cliente['ruc_dni'] ?? 'N/A',
            $placas ?: 'N/A',
            $recibo->data_servicio['nombre'].' '.$recibo->data_servicio['descripcion'] ?? 'N/A',
            $recibo->monto_recibo,
            $recibo->fecha_emision?->format('d/m/Y') ?? 'N/A',
            $recibo->fecha_vencimiento?->format('d/m/Y') ?? 'N/A',
            ucfirst($recibo->estado_recibo ?? 'N/A'),
            $recibo->metodo_pago ?? 'N/A',
            $recibo->fecha_pago?->format('d/m/Y') ?? 'N/A',
            $recibo->observaciones_pago ?? '',
        ];
    }
}
