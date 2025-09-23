<?php

namespace App\View\Composers;

use App\Models\Configuracion;
use Illuminate\View\View;

class ConfiguracionViewComposer
{
    /**
     * La configuración de empresa
     */
    protected $configuracion;

    /**
     * Create a new profile composer.
     */
    public function __construct()
    {
        $this->configuracion = Configuracion::obtenerEmpresa();
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $view->with('empresaConfig', $this->configuracion);
    }
}
