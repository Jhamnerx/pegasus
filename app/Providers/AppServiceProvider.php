<?php

namespace App\Providers;

use App\View\Composers\ConfiguracionViewComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar View Composer para configuración de empresa
        View::composer([
            'components.app.sidebar',
            'livewire.auth.login',
            'layouts.guest'
        ], ConfiguracionViewComposer::class);
    }
}
