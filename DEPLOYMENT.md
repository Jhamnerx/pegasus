# Guía de Despliegue - Pegasus GPS System
## 🚀 Despliegue en cPanel SIN acceso a Terminal

### 1. Configurar Git Repository en cPanel

1. **Accede a cPanel**
2. **Busca "Git™ Version Control"** en la sección Files
3. **Hacer clic en "Create"**
4. **Llenar los campos:**
   - Repository URL: `https://github.com/Jhamnerx/pegasus.git`
   - Repository Path: `/repositories/pegasus` (o el nombre que prefieras)  
   - Branch: `master`
5. **Hacer clic en "Create"**
6. **Esperar a que termine la clonación del repositorio**

### 2. Configurar Variables de Entorno (usando File Manager)

1. **Abrir File Manager** en cPanel
2. **Navegar a la carpeta** `/repositories/pegasus` (donde se clonó el repo)
3. **Buscar el archivo** `.env.production.example`
4. **Hacer clic derecho** en `.env.production.example` → **Copy**
5. **Pegar en la misma carpeta** y renombrar la copia a `.env`
6. **Hacer clic derecho** en `.env` → **Edit**
7. **Modificar los valores** con tu configuración:

```env
APP_NAME="Pegasus GPS"
APP_ENV=production
APP_KEY=base64:GENERAR_ESTO_MAS_ADELANTE
APP_DEBUG=false
APP_TIMEZONE=America/Lima
APP_URL=https://tudominio.com

# Database - Obtener estos datos de cPanel → MySQL Databases
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_usuario_nombre_bd
DB_USERNAME=tu_usuario_mysql
DB_PASSWORD=tu_password_mysql

# Mail - Configurar según tu hosting
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=tu_password_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp
WHATSAPP_API_URL=http://messages.synthesisgroup.pe/send-message
WHATSAPP_API_KEY=tu_api_key_whatsapp
WHATSAPP_SENDER=51915274968

# Jobs
ALERT_DAYS=15,7,3,1
```

8. **Guardar** el archivo `.env`

### 3. Generar APP_KEY (usando cPanel Terminal o PHP Web)

**Opción A: Si tienes Terminal en cPanel:**
```bash
cd /home/tu_usuario/repositories/pegasus
php artisan key:generate
```

**Opción B: Sin Terminal - Crear archivo PHP temporal:**
1. **En File Manager**, crear un archivo llamado `generate_key.php` en la carpeta del proyecto
2. **Contenido del archivo:**
```php
<?php
require_once 'vendor/autoload.php';
$key = 'base64:' . base64_encode(random_bytes(32));
echo "APP_KEY=" . $key;
file_put_contents('.env.key', $key);
?>
```
3. **Ejecutar** visitando `https://tudominio.com/generate_key.php`
4. **Copiar el resultado** y actualizar APP_KEY en el archivo `.env`
5. **ELIMINAR** el archivo `generate_key.php` por seguridad

### 4. Configurar Base de Datos (usando cPanel)

1. **En cPanel**, ir a **"MySQL Databases"**
2. **Crear una base de datos** nueva
3. **Crear un usuario** MySQL y asignarlo a la base de datos
4. **Actualizar** los datos DB_* en el archivo `.env`

### 5. Instalar Dependencias (usando cPanel PHP Selector)

1. **En cPanel**, buscar **"Select PHP Version"** o **"PHP Selector"**
2. **Seleccionar PHP 8.3** (o superior)
3. **Ir a Extensions** y habilitar las extensiones necesarias:
   - mysqli
   - pdo_mysql
   - zip
   - xml
   - gd
   - fileinfo
   - mbstring
   - curl

4. **Para Composer** (si no está instalado):
   - Contactar soporte técnico del hosting
   - O usar una herramienta web como **"Softaculous"** si está disponible

### 6. Ejecutar Migraciones (crear archivo PHP)

**Crear archivo `migrate.php` en la carpeta del proyecto:**
```php
<?php
// migrate.php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
$exitCode = $kernel->call('migrate', ['--force' => true]);
echo "Migration exit code: " . $exitCode . "\n";
echo "</pre>";
?>
```

**Ejecutar** visitando `https://tudominio.com/migrate.php`
**ELIMINAR** el archivo después de usar

### 7. Enlazar Dominio

1. **En cPanel**, ir a **"Subdomains"** o **"Addon Domains"**
2. **Para el dominio principal:**
   - Ir a **"File Manager"**
   - **Renombrar** la carpeta `public_html` a `public_html_backup`
   - **Crear un symbolic link** o mover el contenido:
     - Opción A: Mover todo de `/repositories/pegasus/public` a una nueva carpeta `public_html`
     - Opción B: En **"File Manager"**, usar **"Link"** para crear enlace simbólico

3. **Para subdominios:**
   - **Document Root:** `/repositories/pegasus/public`

### 8. Configurar Cron Jobs

1. **Ir a "Cron Jobs"** en cPanel
2. **Agregar cron job para Laravel Scheduler:**
   - **Minuto:** `*`
   - **Hora:** `*`
   - **Día:** `*`
   - **Mes:** `*`
   - **Día de la semana:** `*`
   - **Comando:** `cd /home/tu_usuario/repositories/pegasus && php artisan schedule:run >/dev/null 2>&1`

3. **Agregar cron job para Queue Worker:**
   - **Minuto:** `0`
   - **Hora:** `*`
   - **Día:** `*`
   - **Mes:** `*`
   - **Día de la semana:** `*`
   - **Comando:** `cd /home/tu_usuario/repositories/pegasus && php artisan queue:work --daemon --sleep=3 --tries=3 >/dev/null 2>&1 &`

### 9. Configurar Permisos (usando File Manager)

1. **En File Manager**, seleccionar las siguientes carpetas:
   - `storage`
   - `bootstrap/cache`
2. **Hacer clic derecho** → **"Permissions"**
3. **Cambiar permisos a 755** (lectura, escritura, ejecución para propietario; lectura y ejecución para grupo y otros)

### 10. Optimización (crear archivo PHP)

**Crear archivo `optimize.php`:**
```php
<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "Configurando cache...\n";
$kernel->call('config:cache');

echo "Configurando rutas...\n";
$kernel->call('route:cache');

echo "Configurando vistas...\n";
$kernel->call('view:cache');

echo "Creando storage link...\n";
$kernel->call('storage:link');

echo "¡Optimización completada!\n";
echo "</pre>";
?>
```

**Ejecutar** y **eliminar** después de usar.

### 11. Compilar Assets (alternativa sin NPM)

Si no tienes acceso a NPM, los assets ya están compilados en el repositorio en la carpeta `public/build`.

**Solo asegúrate de que la carpeta `public/build` exista y tenga contenido.**

## 🔄 Para Actualizaciones Futuras

1. **En cPanel → Git Version Control**
2. **Buscar tu repositorio**
3. **Hacer clic en "Pull"** para actualizar el código
4. **Ejecutar el archivo `optimize.php`** nuevamente
5. **Ejecutar `migrate.php`** si hay nuevas migraciones

## ⚠️ Troubleshooting

### Si el sitio muestra error 500:
1. Verificar que el archivo `.env` esté configurado correctamente
2. Verificar permisos de `storage` y `bootstrap/cache`
3. Revisar logs en `storage/logs/laravel.log`

### Si no funcionan los jobs:
1. Verificar que los cron jobs estén activos en cPanel
2. Verificar la ruta correcta en los comandos cron
3. Probar ejecutando los archivos PHP directamente

### Si la base de datos no conecta:
1. Verificar credenciales DB_* en `.env`
2. Verificar que el usuario MySQL tenga permisos
3. Probar conexión desde cPanel → phpMyAdmin

## 📞 Notas Importantes

- **SIEMPRE eliminar** los archivos PHP temporales después de usarlos
- **Cambiar permisos** de archivos sensibles a 644
- **Mantener backup** de la configuración antes de actualizaciones
- **Contactar soporte** del hosting si necesitas ayuda con Composer o permisos
