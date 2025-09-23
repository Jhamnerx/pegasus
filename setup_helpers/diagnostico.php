<?php
/**
 * Verificador de configuración de Laravel
 * USAR PARA DIAGNOSTICAR PROBLEMAS
 */

echo "<h2>🔍 Diagnóstico del Sistema Pegasus</h2>";

// Verificar PHP
echo "<h3>🐘 Información de PHP</h3>";
echo "<p><strong>Versión PHP:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Memoria límite:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Tiempo de ejecución:</strong> " . ini_get('max_execution_time') . "s</p>";

// Verificar extensiones requeridas
echo "<h3>🔧 Extensiones PHP</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'curl', 'fileinfo', 'xml', 'zip'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ $ext</p>";
    } else {
        echo "<p style='color: red;'>❌ $ext (FALTA)</p>";
    }
}

// Verificar archivos
echo "<h3>📁 Archivos del Sistema</h3>";
$files = [
    '.env' => __DIR__ . '/../.env',
    'composer.json' => __DIR__ . '/../composer.json',
    'vendor/autoload.php' => __DIR__ . '/../vendor/autoload.php',
    'bootstrap/app.php' => __DIR__ . '/../bootstrap/app.php'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✅ $name</p>";
    } else {
        echo "<p style='color: red;'>❌ $name (FALTA)</p>";
    }
}

// Verificar permisos
echo "<h3>🔒 Permisos de Carpetas</h3>";
$directories = [
    'storage' => __DIR__ . '/../storage',
    'storage/logs' => __DIR__ . '/../storage/logs',
    'storage/framework' => __DIR__ . '/../storage/framework',
    'bootstrap/cache' => __DIR__ . '/../bootstrap/cache'
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<p style='color: green;'>✅ $name - Escribible</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ $name - Solo lectura</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ $name - No existe</p>";
    }
}

// Verificar configuración Laravel
if (file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "<h3>⚙️ Configuración Laravel</h3>";
    
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        
        // Verificar APP_KEY
        $appKey = env('APP_KEY');
        if ($appKey) {
            echo "<p style='color: green;'>✅ APP_KEY configurado</p>";
        } else {
            echo "<p style='color: red;'>❌ APP_KEY no configurado</p>";
        }
        
        // Verificar base de datos
        try {
            $db = $app->make('db');
            $db->connection()->getPdo();
            echo "<p style='color: green;'>✅ Conexión a base de datos OK</p>";
            
            // Verificar migraciones
            $migrationFiles = glob(__DIR__ . '/../database/migrations/*.php');
            echo "<p><strong>Migraciones disponibles:</strong> " . count($migrationFiles) . "</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error de base de datos: " . $e->getMessage() . "</p>";
        }
        
        // Verificar configuración WhatsApp
        $whatsappUrl = env('WHATSAPP_API_URL');
        $whatsappKey = env('WHATSAPP_API_KEY');
        
        if ($whatsappUrl && $whatsappKey) {
            echo "<p style='color: green;'>✅ WhatsApp configurado</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ WhatsApp no configurado completamente</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error cargando Laravel: " . $e->getMessage() . "</p>";
    }
}

// Verificar logs recientes
echo "<h3>📋 Logs Recientes</h3>";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -10);
    
    echo "<pre style='background: #f4f4f4; padding: 10px; max-height: 200px; overflow-y: scroll;'>";
    foreach ($recentLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p>No hay archivo de logs disponible</p>";
}

echo "<hr>";
echo "<p><em>Diagnóstico completado - " . date('Y-m-d H:i:s') . "</em></p>";
?>