<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReciboController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('recibos.index');
    }

    /**
     * Generar y mostrar el PDF del recibo (ruta autenticada).
     */
    public function showPdf(Recibo $recibo)
    {
        // Cargar las relaciones necesarias
        $recibo->load(['detalles']);

        // Obtener configuración de empresa desde la base de datos
        $empresaConfig = $this->obtenerConfiguracionEmpresa();


        // Generar el PDF
        $pdf = Pdf::loadView('pdf.recibo', compact('recibo', 'empresaConfig'));

        // Configurar el PDF
        $pdf->setPaper('a4', 'portrait');

        // Retornar el PDF para visualizar en el navegador
        return $pdf->stream('recibo-' . $recibo->numero_recibo . '.pdf');
    }

    /**
     * Obtener configuración de empresa
     */
    private function obtenerConfiguracionEmpresa(): array
    {
        try {
            $configuracion = DB::table('configuraciones')->first();

            if ($configuracion) {
                return [
                    'razon_social' => $configuracion->razon_social,
                    'direccion' => $configuracion->direccion,
                    'telefono' => $configuracion->telefono,
                    'email' => $configuracion->email,
                    'logo' => $configuracion->logo,
                    'metodos_pago' => $configuracion->metodos_pago ? json_decode($configuracion->metodos_pago, true) : []
                ];
            }
        } catch (\Exception $e) {
            // Log del error para debugging
            Log::warning("Error obteniendo configuración de empresa: " . $e->getMessage());
        }

        // Si no hay datos en BD o hay error, usar valores por defecto
        return [
            'razon_social' => config('app.name', 'PEGASUS S.A.C.'),
            'direccion' => 'Dirección de la empresa',
            'telefono' => null,
            'email' => null,
            'logo' => null,
            'metodos_pago' => []
        ];
    }
}
