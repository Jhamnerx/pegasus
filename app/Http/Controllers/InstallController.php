<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    /**
     * Mostrar la página de instalación
     */
    public function index()
    {
        // Verificar si ya está instalado
        if ($this->isAlreadyInstalled()) {
            return view('install.already-installed');
        }

        return view('install.index');
    }

    /**
     * Ejecutar el proceso de instalación
     */
    public function install(Request $request)
    {
        // Verificar si ya está instalado
        if ($this->isAlreadyInstalled()) {
            return response()->json([
                'success' => false,
                'message' => 'La aplicación ya está instalada.',
            ]);
        }

        $steps = [];
        $success = true;
        $errorMessage = '';

        try {
            // Paso 1: Verificar requisitos del sistema
            $steps[] = $this->checkSystemRequirements();

            // Paso 2: Verificar archivo .env
            $steps[] = $this->checkEnvironmentFile();

            // Paso 3: Generar APP_KEY si no existe
            $steps[] = $this->generateApplicationKey();

            // Paso 4: Verificar conexión a base de datos
            $steps[] = $this->checkDatabaseConnection();

            // Paso 5: Ejecutar migraciones
            $steps[] = $this->runMigrations();

            // Paso 6: Ejecutar seeders
            $steps[] = $this->runSeeders();

            // Paso 7: Cambiar SESSION_DRIVER a database
            $steps[] = $this->updateSessionDriver();

            // Paso 8: Crear enlace simbólico de storage
            $steps[] = $this->createStorageLink();

            // Paso 9: Optimizar aplicación
            $steps[] = $this->optimizeApplication();

            // Paso 10: Crear archivo de instalación completada
            $steps[] = $this->markAsInstalled();
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
            $steps[] = [
                'name' => 'Error durante la instalación',
                'status' => 'error',
                'message' => $e->getMessage(),
                'details' => $e->getTraceAsString(),
            ];
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Instalación completada exitosamente' : 'Error durante la instalación',
            'error' => $errorMessage,
            'steps' => $steps,
        ]);
    }

    /**
     * Ejecutar actualización del sistema
     */
    public function update(Request $request)
    {
        $steps = [];
        $success = true;
        $errorMessage = '';

        try {
            // Paso 1: Ejecutar migraciones
            $steps[] = $this->runMigrations();

            // Paso 2: Limpiar cache de vistas
            $steps[] = $this->clearViewCache();

            // Paso 3: Regenerar cache de configuración
            $steps[] = $this->regenerateConfigCache();
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
            $steps[] = [
                'name' => 'Error durante la actualización',
                'status' => 'error',
                'message' => $e->getMessage(),
                'details' => $e->getTraceAsString(),
            ];
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Actualización completada exitosamente' : 'Error durante la actualización',
            'error' => $errorMessage,
            'steps' => $steps,
        ]);
    }

    /**
     * Optimizar la aplicación únicamente
     */
    public function optimize(Request $request)
    {
        $steps = [];
        $success = true;
        $errorMessage = '';

        try {
            // Ejecutar optimización de la aplicación
            $steps[] = $this->optimizeApplication();
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
            $steps[] = [
                'name' => 'Error durante la optimización',
                'status' => 'error',
                'message' => $e->getMessage(),
                'details' => $e->getTraceAsString(),
            ];
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Optimización completada exitosamente' : 'Error durante la optimización',
            'error' => $errorMessage,
            'steps' => $steps,
        ]);
    }

    /**
     * Verificar requisitos del sistema
     */
    private function checkSystemRequirements(): array
    {
        $requirements = [
            'PHP 8.1+' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Ctype Extension' => extension_loaded('ctype'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
            'JSON Extension' => extension_loaded('json'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'cURL Extension' => extension_loaded('curl'),
        ];

        $missing = [];
        foreach ($requirements as $requirement => $satisfied) {
            if (! $satisfied) {
                $missing[] = $requirement;
            }
        }

        return [
            'name' => 'Verificar requisitos del sistema',
            'status' => empty($missing) ? 'success' : 'error',
            'message' => empty($missing)
                ? 'Todos los requisitos están satisfechos'
                : 'Faltan requisitos: ' . implode(', ', $missing),
            'details' => $requirements,
        ];
    }

    /**
     * Verificar archivo .env
     */
    private function checkEnvironmentFile(): array
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            // Intentar copiar desde .env.example
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
                $message = 'Archivo .env creado desde .env.example';
            } else {
                throw new Exception('No se encontró archivo .env ni .env.example');
            }
        } else {
            $message = 'Archivo .env encontrado';
        }

        return [
            'name' => 'Verificar archivo de configuración',
            'status' => 'success',
            'message' => $message,
        ];
    }

    /**
     * Generar APP_KEY
     */
    private function generateApplicationKey(): array
    {
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
            $message = 'APP_KEY generada exitosamente';
        } else {
            $message = 'APP_KEY ya existe';
        }

        return [
            'name' => 'Generar clave de aplicación',
            'status' => 'success',
            'message' => $message,
        ];
    }

    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'name' => 'Verificar conexión a base de datos',
                'status' => 'success',
                'message' => 'Conexión a base de datos exitosa',
            ];
        } catch (Exception $e) {
            throw new Exception('Error conectando a la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar migraciones
     */
    private function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return [
                'name' => 'Ejecutar migraciones de base de datos',
                'status' => 'success',
                'message' => 'Migraciones ejecutadas exitosamente',
                'details' => $output,
            ];
        } catch (Exception $e) {
            throw new Exception('Error ejecutando migraciones: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar seeders esenciales
     */
    private function runSeeders(): array
    {
        try {
            $seeders = [
                'RolesSeeder',
                'ConfiguracionesSeeder',
                'UsersSeeder',
            ];

            $output = '';
            foreach ($seeders as $seeder) {
                Artisan::call('db:seed', [
                    '--class' => $seeder,
                    '--force' => true,
                ]);
                $output .= "✓ {$seeder} ejecutado exitosamente\n";
            }

            return [
                'name' => 'Ejecutar seeders de configuración inicial',
                'status' => 'success',
                'message' => 'Datos iniciales creados exitosamente',
                'details' => $output,
            ];
        } catch (Exception $e) {
            throw new Exception('Error ejecutando seeders: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar SESSION_DRIVER de file a database después de migraciones
     */
    private function updateSessionDriver(): array
    {
        try {
            $envPath = base_path('.env');

            if (File::exists($envPath)) {
                $envContent = File::get($envPath);

                // Cambiar SESSION_DRIVER de file a database
                $newContent = preg_replace(
                    '/^SESSION_DRIVER=file$/m',
                    'SESSION_DRIVER=database',
                    $envContent
                );

                // Solo actualizar si hubo cambio
                if ($newContent !== $envContent) {
                    File::put($envPath, $newContent);

                    $message = 'SESSION_DRIVER cambiado a database exitosamente';
                } else {
                    $message = 'SESSION_DRIVER ya estaba configurado como database';
                }
            } else {
                throw new Exception('Archivo .env no encontrado');
            }

            return [
                'name' => 'Configurar sesiones de base de datos',
                'status' => 'success',
                'message' => $message,
            ];
        } catch (Exception $e) {
            // No es crítico si esto falla
            return [
                'name' => 'Configurar sesiones de base de datos',
                'status' => 'warning',
                'message' => 'No se pudo cambiar SESSION_DRIVER: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Crear enlace simbólico de storage
     */
    private function createStorageLink(): array
    {
        try {
            if (! File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
                $message = 'Enlace simbólico de storage creado';
            } else {
                $message = 'Enlace simbólico de storage ya existe';
            }

            return [
                'name' => 'Crear enlace simbólico de storage',
                'status' => 'success',
                'message' => $message,
            ];
        } catch (Exception $e) {
            // En algunos hostings el enlace simbólico puede fallar, pero no es crítico
            return [
                'name' => 'Crear enlace simbólico de storage',
                'status' => 'warning',
                'message' => 'No se pudo crear enlace simbólico: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Optimizar aplicación
     */
    private function optimizeApplication(): array
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return [
                'name' => 'Optimizar aplicación',
                'status' => 'success',
                'message' => 'Aplicación optimizada para producción',
            ];
        } catch (Exception $e) {
            return [
                'name' => 'Optimizar aplicación',
                'status' => 'warning',
                'message' => 'Optimización parcial: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Marcar como instalado
     */
    private function markAsInstalled(): array
    {
        $installFile = storage_path('app/installed');
        File::put($installFile, now()->toDateTimeString());

        return [
            'name' => 'Finalizar instalación',
            'status' => 'success',
            'message' => 'Instalación marcada como completada',
        ];
    }

    /**
     * Verificar si ya está instalado
     */
    private function isAlreadyInstalled(): bool
    {
        return File::exists(storage_path('app/installed'));
    }

    /**
     * Limpiar cache de vistas
     */
    private function clearViewCache(): array
    {
        try {
            Artisan::call('view:clear');
            $output = Artisan::output();

            return [
                'name' => 'Limpiar cache de vistas',
                'status' => 'success',
                'message' => 'Cache de vistas limpiado exitosamente',
                'details' => $output,
            ];
        } catch (Exception $e) {
            return [
                'name' => 'Limpiar cache de vistas',
                'status' => 'warning',
                'message' => 'Error limpiando cache de vistas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Regenerar cache de configuración
     */
    private function regenerateConfigCache(): array
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            $output = Artisan::output();

            return [
                'name' => 'Regenerar cache de configuración',
                'status' => 'success',
                'message' => 'Cache de configuración regenerado exitosamente',
                'details' => $output,
            ];
        } catch (Exception $e) {
            return [
                'name' => 'Regenerar cache de configuración',
                'status' => 'warning',
                'message' => 'Error regenerando cache de configuración: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mostrar página de información del sistema
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'installed' => $this->isAlreadyInstalled(),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'database_connection' => config('database.default'),
        ];

        return response()->json($info);
    }

    /**
     * Limpiar la cola de trabajos
     */
    public function queueClear()
    {
        try {
            Artisan::call('queue:clear');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Cola de trabajos limpiada exitosamente',
                'details' => $output,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error limpiando la cola de trabajos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
