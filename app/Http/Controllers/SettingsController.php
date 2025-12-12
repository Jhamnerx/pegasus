<?php

namespace App\Http\Controllers;

class SettingsController extends Controller
{
    public function profile()
    {
        return view('settings.profile');
    }

    public function empresa()
    {
        return view('settings.empresa');
    }

    public function plantillasMensajes()
    {
        return view('settings.plantillas-mensajes');
    }
}
