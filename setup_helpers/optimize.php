<?php

/**
 * Optimizador de Laravel para producción
 * USAR DESPUÉS DE CADA ACTUALIZACIÓN
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Error: Composer no está instalado.');
}

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "<h2>⚡ Optimizando Laravel para Producción</h2>";

    $commands = [
        'config:cache' => 'Cacheando configuración',
        'route:cache' => 'Cacheando rutas',
        'view:cache' => 'Cacheando vistas',
        'storage:link' => 'Creando enlace simbólico de storage'
    ];

    foreach ($commands as $command => $description) {
        echo "<h3>🔄 $description...</h3>";
        echo "<pre style='background: #f4f4f4; padding: 10px; margin: 5px 0;'>";

        $exitCode = $kernel->call($command);

        echo "</pre>";

        if ($exitCode === 0) {
            echo "<p style='color: green;'>✅ $description completado</p>";
        } else {
            echo "<p style='color: red;'>❌ Error en: $description</p>";
        }
        echo "<hr>";
    }

    // Verificar permisos
    echo "<h3>📁 Verificando Permisos</h3>";
    $directories = [
        __DIR__ . '/../storage',
        __DIR__ . '/../bootstrap/cache'
    ];

    foreach ($directories as $dir) {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✅ " . basename($dir) . " - Permisos OK</p>";
        } else {
            echo "<p style='color: red;'>❌ " . basename($dir) . " - Sin permisos de escritura</p>";
            echo "<p>Configura permisos 755 para: $dir</p>";
        }
    }

    echo "<hr>";
    echo "<h3>🎉 Optimización Completada</h3>";
    echo "<p>Tu aplicación Laravel está optimizada para producción.</p>";

    echo "<hr>";
    echo "<p><strong>💡 Consejo:</strong> Ejecuta este archivo después de cada actualización de código.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
