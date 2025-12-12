<?php

namespace App\Providers;

use App\Services\WhatsAppService;
use Illuminate\Support\ServiceProvider;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('whatsapp', function ($app) {
            return new WhatsAppService;
        });

        // También registrar con el nombre de clase para inyección de dependencias
        $this->app->bind(WhatsAppService::class, function ($app) {
            return $app->make('whatsapp');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
