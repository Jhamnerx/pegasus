<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si la aplicación está instalada
        if (! $this->isInstalled()) {
            // Si la ruta actual NO es de instalación, redirigir
            if (! $request->is('install*')) {
                return redirect()->route('install.index');
            }
        } else {
            // Si ya está instalada y trata de acceder a install, redirigir al dashboard
            if ($request->is('install') && ! $request->is('install/system-info')) {
                return redirect()->route('dashboard.index');
            }
        }

        return $next($request);
    }

    /**
     * Verificar si la aplicación está instalada
     */
    private function isInstalled(): bool
    {
        return File::exists(storage_path('app/installed'));
    }
}
