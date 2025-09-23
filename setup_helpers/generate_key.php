<?php
/**
 * Generador de APP_KEY para Laravel
 * USAR SOLO DURANTE LA INSTALACIÓN Y ELIMINAR DESPUÉS
 */

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Error: Composer no está instalado. Contacta a soporte técnico.');
}

try {
    // Generar clave de 32 bytes
    $key = 'base64:' . base64_encode(random_bytes(32));
    
    echo "<h2>🔑 Clave generada para Laravel</h2>";
    echo "<p><strong>Copia esta línea en tu archivo .env:</strong></p>";
    echo "<code style='background: #f4f4f4; padding: 10px; display: block; margin: 10px 0;'>";
    echo "APP_KEY=" . $key;
    echo "</code>";
    
    // Intentar escribir al archivo .env si existe
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        
        if (strpos($envContent, 'APP_KEY=') !== false) {
            // Reemplazar APP_KEY existente
            $newContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
            
            if (file_put_contents($envPath, $newContent)) {
                echo "<p style='color: green;'>✅ APP_KEY actualizada automáticamente en .env</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ No se pudo escribir automáticamente. Copia manualmente.</p>";
            }
        } else {
            // Agregar APP_KEY al final
            if (file_put_contents($envPath, $envContent . "\nAPP_KEY=" . $key . "\n")) {
                echo "<p style='color: green;'>✅ APP_KEY agregada automáticamente en .env</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Archivo .env no encontrado. Crea el archivo .env primero.</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>🚨 IMPORTANTE:</strong> Elimina este archivo después de usar por seguridad:</p>";
    echo "<p><code>rm " . __FILE__ . "</code></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>