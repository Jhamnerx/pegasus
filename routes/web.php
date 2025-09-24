<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PublicReciboController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SelectController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::post('/run', [InstallController::class, 'install'])->name('install.run');
    Route::get('/update', [InstallController::class, 'update'])->name('install.update');
    Route::get('/optimize', [InstallController::class, 'optimize'])->name('install.optimize');
    Route::get('/system-info', [InstallController::class, 'systemInfo'])->name('install.system-info');
    Route::get('/queue-clear', [InstallController::class, 'queueClear'])->name('install.queue-clear');
});

Route::get('recibo/{uuid}', [PublicReciboController::class, 'pdf'])->name('public.recibo.pdf');
Route::get('recibo/download/{uuid}', [PublicReciboController::class, 'download'])->name('public.recibo.download');

Route::middleware(['auth'])->group(function () {
    // Settings routes
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    // Configuraciones de empresa (solo para administradores)
    Route::middleware(['role:Administrador'])->group(function () {
        Route::get('settings/empresa', [SettingsController::class, 'empresa'])->name('settings.empresa');
        Route::get('settings/plantillas-mensajes', [SettingsController::class, 'plantillasMensajes'])->name('settings.plantillas-mensajes');
    });

    // Rutas principales
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard.index');
    Route::get('/', [DashboardController::class, 'dashboard']);
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    Route::get('/cobros', [CobroController::class, 'index'])->name('cobros.index');
    Route::get('/recibos', [ReciboController::class, 'index'])->name('recibos.index');
    Route::get('/recibos/{recibo}/pdf', [ReciboController::class, 'showPdf'])->name('recibos.pdf');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // Usuarios (solo para administradores)
    Route::middleware(['role:Administrador'])->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    });

    // Rutas para selects (WireUI)
    Route::prefix('select')->name('select.')->group(function () {
        Route::get('clientes', [SelectController::class, 'clientes'])->name('clientes');
        Route::get('servicios', [SelectController::class, 'servicios'])->name('servicios');
        Route::get('cobros', [SelectController::class, 'cobros'])->name('cobros');
        Route::get('recibos', [SelectController::class, 'recibos'])->name('recibos');
        Route::get('metodos-pago', [SelectController::class, 'metodosPago'])->name('metodos-pago');
    });
});

require __DIR__ . '/auth.php';
