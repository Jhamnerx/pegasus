# Guía de Despliegue - Pegasus GPS System

## Pasos para despliegue en cPanel con Git

### 1. Configurar Git Repository en cPanel

1. **Accede a cPanel**
2. **Busca "Git™ Version Control"** en la sección Files
3. **Hacer clic en "Create"**
4. **Llenar los campos:**
   - Repository URL: `https://github.com/Jhamnerx/pegasus.git`
   - Repository Path: `/repositories/pegasus` (o el nombre que prefieras)
   - Branch: `master`
5. **Hacer clic en "Create"**

### 2. Configurar Variables de Entorno

1. **Crear archivo .env** en la carpeta del repositorio clonado:
   ```bash
   cp .env.production.example .env
   ```

2. **Editar el archivo .env** con los datos de tu servidor:
   - APP_URL: Tu dominio real (https://tudominio.com)
   - DB_*: Configuración de tu base de datos MySQL
   - MAIL_*: Configuración de tu servidor de correo
   - WHATSAPP_*: Configuración de tu API de WhatsApp

3. **Generar APP_KEY:**
   ```bash
   php artisan key:generate
   ```

### 3. Instalar Dependencias

```bash
# Instalar dependencias de PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias de Node.js
npm install

# Compilar assets para producción
npm run build
```

### 4. Configurar Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders (opcional)
php artisan db:seed --force
```

### 5. Configurar Permisos

```bash
# Dar permisos a las carpetas necesarias
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Enlazar Dominio

1. **En cPanel, ir a "Subdomains" o "Addon Domains"**
2. **Configurar el Document Root hacia:**
   ```
   /repositories/pegasus/public
   ```
   (Reemplaza "repositories/pegasus" con la ruta donde clonaste el repo)

### 7. Configurar Cron Jobs

1. **Ir a "Cron Jobs" en cPanel**
2. **Agregar estos dos cron jobs:**

   **Para Laravel Scheduler (cada minuto):**
   ```bash
   * * * * * cd /home/tu_usuario/repositories/pegasus && php artisan schedule:run >> /dev/null 2>&1
   ```

   **Para Queue Worker (reinicia cada hora):**
   ```bash
   0 * * * * cd /home/tu_usuario/repositories/pegasus && php artisan queue:restart
   1 * * * * cd /home/tu_usuario/repositories/pegasus && php artisan queue:work --daemon --sleep=3 --tries=3 &
   ```

### 8. Configuraciones Adicionales

1. **Optimizar configuración:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Crear storage link:**
   ```bash
   php artisan storage:link
   ```

### 9. Verificación Final

1. **Verificar que el sitio carga correctamente**
2. **Probar login en el sistema**
3. **Verificar que los jobs funcionan:**
   ```bash
   php artisan queue:work --once
   ```

## Comandos Importantes para Mantener

### Para actualizar código desde Git:
```bash
cd /ruta/a/tu/repositorio
git pull origin master
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Para monitorear colas:
```bash
php artisan queue:work --verbose
```

### Para ejecutar scheduler manualmente:
```bash
php artisan schedule:run
```

## Estructura de Cron Jobs Recomendada

```bash
# Laravel Scheduler - cada minuto
* * * * * cd /ruta/completa/al/proyecto && php artisan schedule:run >> /dev/null 2>&1

# Queue Worker - reiniciar cada hora
0 * * * * cd /ruta/completa/al/proyecto && php artisan queue:restart
1 * * * * cd /ruta/completa/al/proyecto && php artisan queue:work --daemon --sleep=3 --tries=3 > /dev/null 2>&1 &

# Limpiar logs antiguos - cada día a las 2 AM
0 2 * * * cd /ruta/completa/al/proyecto && php artisan log:clear
```

## Troubleshooting

### Si las colas no funcionan:
1. Verificar que los cron jobs estén corriendo
2. Revisar logs: `tail -f storage/logs/laravel.log`
3. Ejecutar manualmente: `php artisan queue:work --once`

### Si el scheduler no funciona:
1. Verificar cron job del scheduler
2. Ejecutar manualmente: `php artisan schedule:run`
3. Listar tareas programadas: `php artisan schedule:list`

### Si WhatsApp no envía mensajes:
1. Verificar configuración WHATSAPP_* en .env
2. Probar con Tinker: `php artisan tinker` -> `app('whatsapp')->sendMessage('51999999999', 'Test')`