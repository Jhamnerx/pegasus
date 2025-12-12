<?php

namespace App\Exports;

use App\Models\Recibo;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RecibosDetalladoExport implements FromQuery, WithHeadings, WithMapping
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
     * Query que retorna los detalles de recibos (una fila por cada línea de detalle)
     */
    public function query()
    {
        $query = Recibo::query()
            ->join('recibos_detalle', 'recibos.id', '=', 'recibos_detalle.recibo_id')
            ->select([
                'recibos.*',
                'recibos_detalle.placa as detalle_placa',
                'recibos_detalle.concepto',
                'recibos_detalle.monto_calculado as detalle_monto',
                'recibos_detalle.fecha_inicio_periodo as detalle_fecha_inicio',
                'recibos_detalle.fecha_fin_periodo as detalle_fecha_fin',
                'recibos_detalle.dias_calculados',
                'recibos_detalle.factor_prorateo',
                'recibos_detalle.es_prorrateo',
            ]);

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

        return $query->orderBy('fecha_emision', 'desc')
            ->orderBy('recibos_detalle.orden');
    }

    public function headings(): array
    {
        return [
            'Número Recibo',
            'Cliente',
            'RUC/DNI',
            'Placa',
            'Concepto',
            'Servicio',
            'Monto Línea',
            'Monto Total Recibo',
            'Fecha Emisión',
            'Fecha Vencimiento',
            'Período Inicio',
            'Período Fin',
            'Días Calculados',
            'Factor Prorrateo',
            'Es Prorrateo',
            'Estado',
            'Método Pago',
            'Fecha Pago',
            'Observaciones',
        ];
    }

    /**
     * Mapea cada línea de detalle como una fila separada
     */
    public function map($fila): array
    {
        return [
            $fila->numero_recibo,
            $fila->data_cliente['nombre_cliente'] ?? 'N/A',
            $fila->data_cliente['ruc_dni'] ?? 'N/A',
            $fila->detalle_placa ?: 'N/A',
            $fila->concepto ?: 'N/A',
            $fila->data_servicio['nombre'].' '.$fila->data_servicio['descripcion'] ?? 'N/A',
            $fila->detalle_monto ?: 0,
            $fila->monto_recibo,
            $fila->fecha_emision?->format('d/m/Y') ?? 'N/A',
            $fila->fecha_vencimiento?->format('d/m/Y') ?? 'N/A',
            $fila->detalle_fecha_inicio ? date('d/m/Y', strtotime($fila->detalle_fecha_inicio)) : 'N/A',
            $fila->detalle_fecha_fin ? date('d/m/Y', strtotime($fila->detalle_fecha_fin)) : 'N/A',
            $fila->dias_calculados ?: 0,
            $fila->factor_prorateo ? round($fila->factor_prorateo, 4) : 1,
            $fila->es_prorrateo ? 'Sí' : 'No',
            ucfirst($fila->estado_recibo ?? 'N/A'),
            $fila->metodo_pago ?? 'N/A',
            $fila->fecha_pago?->format('d/m/Y') ?? 'N/A',
            $fila->observaciones_pago ?? '',
        ];
    }
}
