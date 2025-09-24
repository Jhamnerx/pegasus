<?php

namespace App\Console\Commands;

use App\Jobs\NotifyRecibosVencidosJob;
use App\Jobs\NotifyVencimientoRecibosJob;
use Illuminate\Console\Command;

class SendNotificationRecibos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recibos:send-notifications {--type=all : Tipo de notificación: all, vencimiento, vencidos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar notificaciones de recibos por WhatsApp (vencimiento y vencidos)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');

        $this->info('Iniciando envío de notificaciones de recibos...');

        try {
            switch ($type) {
                case 'vencimiento':
                    $this->info('Enviando notificaciones de próximos vencimientos...');
                    NotifyVencimientoRecibosJob::dispatch();
                    break;

                case 'vencidos':
                    $this->info('Enviando notificaciones de recibos vencidos...');
                    NotifyRecibosVencidosJob::dispatch();
                    break;

                case 'all':
                default:
                    $this->info('Enviando notificaciones de próximos vencimientos...');
                    NotifyVencimientoRecibosJob::dispatch();

                    $this->info('Enviando notificaciones de recibos vencidos...');
                    NotifyRecibosVencidosJob::dispatch();
                    break;
            }

            $this->info('✓ Jobs de notificación enviados a la cola correctamente.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al enviar notificaciones: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
