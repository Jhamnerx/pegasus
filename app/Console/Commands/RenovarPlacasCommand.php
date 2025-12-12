<?php

namespace App\Console\Commands;

use App\Jobs\RenovarCobroPlacasJob;
use Illuminate\Console\Command;

class RenovarPlacasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cobros:renovar-placas {--sync : Ejecutar sincronamente sin queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renovar automáticamente las placas vencidas de cobros activos que ya tienen recibos generados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando renovación de placas vencidas...');

        if ($this->option('sync')) {
            // Ejecutar sincrónicamente
            $job = new RenovarCobroPlacasJob;
            $resultado = $job->handle();

            if ($resultado) {
                $this->info('✅ Renovación completada exitosamente');
            } else {
                $this->warn('⚠️  No se encontraron placas para renovar');
            }
        } else {
            // Despachar al queue
            RenovarCobroPlacasJob::dispatch();
            $this->info('✅ Job despachado a la cola. Verifica los logs para más detalles.');
        }

        return self::SUCCESS;
    }
}
