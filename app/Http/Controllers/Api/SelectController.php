<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cobro;
use App\Models\Recibo;
use App\Models\Servicio;
use Illuminate\Http\Request;

class SelectController extends Controller
{
    /**
     * Obtener clientes para select
     */
    public function clientes(Request $request)
    {
        return Cliente::query()
            ->select('id', 'nombre_cliente', 'ruc_dni', 'correo_electronico', 'telefono', 'direccion')
            ->orderBy('nombre_cliente')
            ->when(
                $request->search,
                fn ($query) => $query
                    ->where('nombre_cliente', 'like', "%{$request->search}%")
                    ->orWhere('ruc_dni', 'like', "%{$request->search}%")
            )
            ->when(
                $request->exists('selected'),
                fn ($query) => $query->whereIn('id', $request->input('selected', [])),
            )
            ->where('estado', 'Activo')
            ->limit(50)
            ->get()
            ->map(function ($cliente) {
                return [
                    'value' => $cliente->id,
                    'label' => $cliente->nombre_cliente.' ('.$cliente->ruc_dni.')',
                    'description' => $cliente->correo_electronico ?? 'Sin email',
                ];
            });
    }

    /**
     * Obtener servicios para select
     */
    public function servicios(Request $request)
    {
        return Servicio::query()
            ->select('id', 'nombre_servicio', 'descripcion', 'precio_base')
            ->orderBy('nombre_servicio')
            ->when(
                $request->search,
                fn ($query) => $query
                    ->where('nombre_servicio', 'like', "%{$request->search}%")
                    ->orWhere('descripcion', 'like', "%{$request->search}%")
            )
            ->when(
                $request->exists('selected'),
                fn ($query) => $query->whereIn('id', $request->input('selected', [])),
            )
            ->where('activo', true)
            ->limit(50)
            ->get()
            ->map(function ($servicio) {
                return [
                    'value' => $servicio->id,
                    'label' => $servicio->nombre_servicio,
                    'description' => 'S/ '.number_format($servicio->precio_base, 2),
                ];
            });
    }

    /**
     * Obtener cobros para select
     */
    public function cobros(Request $request)
    {
        return Cobro::query()
            ->select('id', 'cliente_id', 'servicio_id', 'placa', 'monto_total', 'periodo_facturacion', 'fecha_inicio_periodo', 'fecha_fin_periodo', 'factor_prorateo')
            ->with(['cliente:id,nombre_cliente,ruc_dni', 'servicio:id,nombre_servicio'])
            ->orderBy('created_at', 'desc')
            ->when(
                $request->search,
                fn ($query) => $query
                    ->whereHas('cliente', function ($q) use ($request) {
                        $q->where('nombre_cliente', 'like', "%{$request->search}%")
                            ->orWhere('ruc_dni', 'like', "%{$request->search}%");
                    })
                    ->orWhere('placa', 'like', "%{$request->search}%")
                    ->orWhereHas('servicio', function ($q) use ($request) {
                        $q->where('nombre_servicio', 'like', "%{$request->search}%");
                    })
            )
            ->when(
                $request->exists('selected'),
                fn ($query) => $query->whereIn('id', $request->input('selected', [])),
            )
            ->where('estado', 'activo')
            ->limit(50)
            ->get()
            ->map(function ($cobro) {
                return [
                    'value' => $cobro->id,
                    'label' => ($cobro->cliente?->nombre_cliente ?? 'Sin cliente').
                        ' - '.($cobro->servicio?->nombre_servicio ?? 'Sin servicio').
                        ' - S/ '.number_format($cobro->monto_total, 2),
                    'description' => 'Placa: '.($cobro->placa ?? 'N/A').
                        ' | Período: '.($cobro->periodo_facturacion ?? 'N/A'),
                ];
            });
    }

    /**
     * Obtener recibos para select
     */
    public function recibos(Request $request)
    {
        return Recibo::query()
            ->select('id', 'numero_recibo', 'data_cliente', 'data_cobro', 'monto_recibo', 'fecha_vencimiento', 'estado_recibo')
            ->orderBy('created_at', 'desc')
            ->when(
                $request->search,
                fn ($query) => $query
                    ->where('numero_recibo', 'like', "%{$request->search}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.nombre_cliente')) LIKE ?", ["%{$request->search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cliente, '$.ruc_dni')) LIKE ?", ["%{$request->search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_cobro, '$.placa')) LIKE ?", ["%{$request->search}%"])
            )
            ->when(
                $request->estado,
                function ($query) use ($request) {
                    if ($request->estado === 'vencidos') {
                        $query->where('fecha_vencimiento', '<', now())
                            ->where('estado_recibo', '!=', 'pagado');
                    } else {
                        $query->where('estado_recibo', $request->estado);
                    }
                }
            )
            ->when(
                $request->exists('selected'),
                fn ($query) => $query->whereIn('id', $request->input('selected', [])),
            )
            ->limit(50)
            ->get()
            ->map(function ($recibo) {
                $clienteNombre = $recibo->data_cliente['nombre_cliente'] ?? 'Sin cliente';
                $placa = $recibo->data_cobro['placa'] ?? 'Sin placa';

                return [
                    'value' => $recibo->id,
                    'label' => $recibo->numero_recibo.
                        ' - '.$clienteNombre.
                        ' - S/ '.number_format($recibo->monto_recibo, 2),
                    'description' => 'Estado: '.ucfirst($recibo->estado_recibo).
                        ' | Vencimiento: '.($recibo->fecha_vencimiento?->format('d/m/Y') ?? 'N/A'),
                ];
            });
    }
}
