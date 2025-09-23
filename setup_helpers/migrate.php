<?php
/**
 * Migrador de base de datos para Laravel
 * USAR SOLO DURANTE LA INSTALACIÓN Y ELIMINAR DESPUÉS
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Error: Composer no está instalado. Las dependencias deben instalarse primero.');
}

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<h2>🗃️ Ejecutando Migraciones de Base de Datos</h2>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border-left: 4px solid #007cba;'>";
    
    // Ejecutar migraciones
    $exitCode = $kernel->call('migrate', [
        '--force' => true,
        '--no-interaction' => true
    ]);
    
    echo "</pre>";
    
    if ($exitCode === 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Migraciones ejecutadas exitosamente</p>";
        
        // Mostrar estado de las migraciones
        echo "<h3>📋 Estado de las migraciones:</h3>";
        echo "<pre style='background: #f9f9f9; padding: 10px;'>";
        $kernel->call('migrate:status');
        echo "</pre>";
        
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Error en las migraciones (código: $exitCode)</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>🚨 IMPORTANTE:</strong> Elimina este archivo después de usar:</p>";
    echo "<p><code>rm " . __FILE__ . "</code></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>El archivo .env esté configurado correctamente</li>";
    echo "<li>La base de datos exista y sea accesible</li>";
    echo "<li>Las credenciales de DB sean correctas</li>";
    echo "</ul>";
}
?>