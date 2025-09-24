<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Middleware global para verificar instalación
        $middleware->web(append: [
            \App\Http\Middleware\CheckInstallation::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Crear recibos diariamente a las 9:00 AM
        $schedule->job(new \App\Jobs\CreateRecibosJob())
            ->dailyAt('09:00')
            ->name('crear-recibos-diarios')
            ->withoutOverlapping();

        $schedule->job(new \App\Jobs\CreateRecibosJob())
            ->everyMinute()
            ->name('crear-recibos-diarios');

        // Notificar vencimientos a las 9:30 AM
        $schedule->job(new \App\Jobs\NotifyVencimientoRecibosJob())
            ->dailyAt('09:30')
            ->name('notificar-vencimientos')
            ->withoutOverlapping();
        $schedule->job(new \App\Jobs\NotifyRecibosVencidosJob())
            ->dailyAt('09:30')
            ->name('notificar-vencimientos')
            ->withoutOverlapping();

        // Procesar queue cada 5 minutos
        $schedule->command('queue:work --stop-when-empty --max-time=300')
            ->everyFiveMinutes()
            ->name('procesar-colas')
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
