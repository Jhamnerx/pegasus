<?php

namespace App\Jobs;

use App\Models\Cobro;
use App\Models\CobroPlaca;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenovarCobroPlacasJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * Este job busca cobros activos cuyas placas ya vencieron y que tienen recibos generados
     * para crear automáticamente nuevas placas para el siguiente periodo.
     */
    public function handle(): bool
    {
        try {
            Log::info('RenovarCobroPlacasJob: Iniciando renovación de placas vencidas');

            // Buscar cobros activos que tengan placas vencidas CON recibos generados
            $cobros = Cobro::activos()
                ->whereHas('cobroPlacas', function ($query) {
                    // Placas que ya vencieron
                    $query->whereDate('fecha_fin', '<', Carbon::now());
                })
                ->whereHas('recibos', function ($query) {
                    // Que tengan recibos generados (pagados o pendientes)
                    $query->whereIn('estado_recibo', ['pendiente', 'pagado', 'vencido']);
                })
                ->with(['cobroPlacas', 'recibos'])
                ->get();

            if ($cobros->isEmpty()) {
                Log::info('RenovarCobroPlacasJob: No hay cobros que necesiten renovación de placas');

                return false;
            }

            $placasRenovadas = 0;

            foreach ($cobros as $cobro) {
                DB::beginTransaction();
                try {
                    $renovadas = $this->renovarPlacasCobro($cobro);
                    $placasRenovadas += $renovadas;
                    DB::commit();

                    if ($renovadas > 0) {
                        Log::info("Cobro {$cobro->id}: {$renovadas} placas renovadas para el siguiente periodo");
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Error renovando placas del cobro {$cobro->id}: " . $e->getMessage());
                }
            }

            Log::info("RenovarCobroPlacasJob completado. Total de placas renovadas: {$placasRenovadas}");

            return $placasRenovadas > 0;
        } catch (\Exception $e) {
            Log::error('Error en RenovarCobroPlacasJob: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Renovar las placas vencidas de un cobro específico
     */
    private function renovarPlacasCobro(Cobro $cobro): int
    {
        // Obtener placas vencidas que NO hayan sido renovadas aún
        $placasVencidas = $cobro->cobroPlacas()
            ->whereDate('fecha_fin', '<', Carbon::now())
            ->get();

        $placasRenovadas = 0;

        foreach ($placasVencidas as $placaVencida) {
            // Verificar que tenga al menos un recibo generado
            $tieneRecibo = $placaVencida->recibos()
                ->whereHas('recibo', function ($query) {
                    $query->whereIn('estado_recibo', ['pendiente', 'pagado', 'vencido']);
                })
                ->exists();

            if (! $tieneRecibo) {
                continue; // No renovar si no tiene recibos generados
            }

            // Verificar que no exista ya una renovación para esta placa
            $yaRenovada = CobroPlaca::where('cobro_id', $cobro->id)
                ->where('placa', $placaVencida->placa)
                ->whereDate('fecha_inicio', '>', $placaVencida->fecha_fin)
                ->exists();

            if ($yaRenovada) {
                continue; // Ya fue renovada
            }

            // Calcular nuevas fechas según el periodo de facturación
            $nuevaFechaInicio = Carbon::parse($placaVencida->fecha_fin)->addDay();
            $nuevaFechaFin = $this->calcularFechaFin($nuevaFechaInicio, $cobro->periodo_facturacion);

            // Crear nueva placa para el siguiente periodo
            $nuevaPlaca = CobroPlaca::create([
                'cobro_id' => $cobro->id,
                'placa' => $placaVencida->placa,
                'fecha_inicio' => $nuevaFechaInicio,
                'fecha_fin' => $nuevaFechaFin,
                'monto_calculado' => $placaVencida->monto_calculado,
                'dias_prorrateados' => $this->calcularDias($nuevaFechaInicio, $nuevaFechaFin),
                'factor_prorateo' => 1.0000, // Por defecto sin prorrateo
                'observaciones' => "Renovación automática de placa {$placaVencida->placa}",
            ]);

            // Si el cobro tiene prorrateo habilitado, recalcular
            if ($cobro->tiene_prorateo) {
                $nuevaPlaca->aplicarProrrateo();
            }

            $placasRenovadas++;

            Log::info("Placa {$placaVencida->placa} renovada: {$nuevaFechaInicio->format('Y-m-d')} a {$nuevaFechaFin->format('Y-m-d')}");
        }

        return $placasRenovadas;
    }

    /**
     * Calcular fecha fin según el periodo de facturación
     */
    private function calcularFechaFin(Carbon $fechaInicio, ?string $periodoFacturacion): Carbon
    {
        return match ($periodoFacturacion) {
            'Mensual' => $fechaInicio->copy()->addMonth()->subDay(),
            'Bimestral' => $fechaInicio->copy()->addMonths(2)->subDay(),
            'Trimestral' => $fechaInicio->copy()->addMonths(3)->subDay(),
            'Semestral' => $fechaInicio->copy()->addMonths(6)->subDay(),
            'Anual' => $fechaInicio->copy()->addYear()->subDay(),
            default => $fechaInicio->copy()->addMonth()->subDay(), // Default: mensual
        };
    }

    /**
     * Calcular días entre dos fechas
     */
    private function calcularDias(Carbon $fechaInicio, Carbon $fechaFin): int
    {
        return $fechaInicio->diffInDays($fechaFin) + 1;
    }
}
