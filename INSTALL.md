# 🚀 Guía de Instalación - Pegasus GPS

Esta guía detalla cómo instalar el sistema Pegasus GPS en un servidor AlmaLinux 9.5 desde cero.

## 📋 Requisitos del Servidor

-   **Sistema Operativo:** AlmaLinux 9.5 / Rocky Linux 9 / RHEL 9
-   **RAM:** Mínimo 2GB (recomendado 4GB)
-   **Disco:** Mínimo 20GB
-   **CPU:** 2 cores recomendado
-   **Acceso:** Root o sudo

## 🎯 Instalación Automática (Recomendado)

### Paso 1: Conectarse al Servidor y Clonar el Repositorio

**IMPORTANTE:** El script de instalación debe ejecutarse DENTRO del repositorio clonado.

```bash
# Conectarse al servidor vía SSH
ssh root@tu-servidor.com

# Instalar git si no está instalado
dnf install -y git

# Clonar el repositorio
git clone https://github.com/Jhamnerx/pegasus
cd pegasus
```

### Paso 2: Ejecutar el Script de Instalación

```bash
# Ejecutar el script
sudo bash install-server.sh
```

El script te pedirá:

-   **Dominio:** El dominio donde se instalará el sistema (ej: pegasus.tuempresa.com)
-   **SSL:** Si deseas configurar certificado SSL automáticamente

### Paso 3: Esperar la Instalación

El proceso toma aproximadamente **15-20 minutos** y realiza:

-   ✅ Actualización del sistema AlmaLinux
-   ✅ Instalación de repositorio EPEL y Remi
-   ✅ Clonación automática del repositorio desde GitHub
-   ✅ Instalación de PHP 8.3 con todas las extensiones necesarias
-   ✅ Instalación de Apache (httpd) con mod_rewrite y mod_ssl
-   ✅ Instalación de MySQL 8.0
-   ✅ Instalación de Redis
-   ✅ Instalación de Supervisor (para colas y scheduler)
-   ✅ Instalación de Composer y NPM
-   ✅ Instalación de dependencias (composer + npm)
-   ✅ Compilación de assets
-   ✅ Configuración de base de datos
-   ✅ Ejecución de migraciones
-   ✅ Configuración de VirtualHost
-   ✅ Configuración de Supervisor para colas
-   ✅ Instalación de phpMyAdmin
-   ✅ Configuración de SELinux
-   ✅ Configuración de SSL (opcional)
-   ✅ Configuración de Firewall (FirewallD)

### Paso 4: Guardar Credenciales

Al finalizar, el script generará un archivo con todas las credenciales:

```bash
cat /root/CREDENCIALES.txt
```

**⚠️ IMPORTANTE:** Guarda este archivo en un lugar seguro y elimínalo del servidor después:

```bash
# Borrar el archivo de credenciales
rm /root/CREDENCIALES.txt
```

---

## 🔧 Instalación Manual

Si prefieres instalar manualmente o necesitas personalizar el proceso:

### 1️⃣ Actualizar el Sistema

```bash
dnf update -y
dnf install -y epel-release
```

### 2️⃣ Instalar Repositorio Remi para PHP 8.3

```bash
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
dnf module reset php -y
dnf module enable php:remi-8.3 -y
```

### 3️⃣ Instalar PHP 8.3 y Extensiones

```bash
dnf install -y \
    php \
    php-cli \
    php-fpm \
    php-mysqlnd \
    php-pdo \
    php-xml \
    php-mbstring \
    php-curl \
    php-json \
    php-zip \
    php-gd \
    php-bcmath \
    php-intl \
    php-redis \
    php-soap \
    php-imagick \
    php-opcache
```

### 4️⃣ Instalar Apache (httpd)

```bash
dnf install -y httpd mod_ssl

# Habilitar y arrancar Apache
systemctl enable httpd
systemctl start httpd

# Verificar instalación
php -v
httpd -v
```

### 5️⃣ Instalar MySQL 8.0

```bash
dnf install -y mysql-server

# Habilitar y arrancar MySQL
systemctl enable mysqld
systemctl start mysqld

# Configuración segura
mysql_secure_installation
```

### 6️⃣ Crear Base de Datos y Usuario

```bash
mysql -u root -p
```

```sql
CREATE DATABASE pegasus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pegasus'@'localhost' IDENTIFIED BY 'TU_CONTRASEÑA_SEGURA';
GRANT ALL PRIVILEGES ON pegasus.* TO 'pegasus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 7️⃣ Instalar Redis

```bash
dnf install -y redis

# Habilitar y arrancar Redis
systemctl enable redis
systemctl start redis
```

### 8️⃣ Instalar Supervisor

```bash
dnf install -y supervisor

# Habilitar y arrancar Supervisor
systemctl enable supervisord
systemctl start supervisord
```

### 9️⃣ Instalar Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 🔟 Instalar Node.js y NPM

```bash
dnf module install -y nodejs:20
```

### 1️⃣1️⃣ Clonar el Repositorio

```bash
cd /var/www
git clone https://github.com/Jhamnerx/pegasus
cd pegasus
```

### 1️⃣2️⃣ Configurar Permisos

```bash
chown -R apache:apache /var/www/pegasus
chmod -R 755 /var/www/pegasus
chmod -R 775 /var/www/pegasus/storage
chmod -R 775 /var/www/pegasus/bootstrap/cache
```

### 1️⃣3️⃣ Instalar Dependencias

```bash
cd /var/www/pegasus

# Composer
composer install --optimize-autoloader --no-dev

# NPM
npm install
npm run build
```

### 1️⃣4️⃣ Configurar Laravel

```bash
# Copiar archivo de entorno
cp .env.example .env

# Generar key
php artisan key:generate

# Editar configuración
nano .env
```

Configuración mínima requerida en `.env`:

```env
APP_NAME="Pegasus GPS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pegasus
DB_USERNAME=pegasus
DB_PASSWORD=TU_CONTRASEÑA_MYSQL

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 1️⃣5️⃣ Ejecutar Migraciones y Seeders

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 1️⃣6️⃣ Configurar VirtualHost en Apache

```bash
nano /etc/httpd/conf.d/pegasus.conf
```

Contenido:

```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    ServerAlias www.tu-dominio.com
    DocumentRoot /var/www/pegasus/public

    <Directory /var/www/pegasus/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    ErrorLog /var/log/httpd/pegasus-error.log
    CustomLog /var/log/httpd/pegasus-access.log combined
</VirtualHost>
```

Reiniciar Apache:

```bash
systemctl restart httpd
```

### 1️⃣7️⃣ Configurar Supervisor para Laravel

```bash
nano /etc/supervisord.d/pegasus.ini
```

Contenido:

```ini
[program:pegasus-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pegasus/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=apache
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pegasus/storage/logs/worker.log
stopwaitsecs=3600

[program:pegasus-scheduler]
process_name=%(program_name)s
command=sh -c 'while [ true ]; do (php /var/www/pegasus/artisan schedule:run --verbose --no-interaction &); sleep 60; done'
autostart=true
autorestart=true
user=apache
redirect_stderr=true
stdout_logfile=/var/www/pegasus/storage/logs/scheduler.log
```

Recargar Supervisor:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start all
```

### 1️⃣8️⃣ Configurar SELinux

```bash
# Permitir a Apache escribir en directorios de Laravel
chcon -R -t httpd_sys_rw_content_t /var/www/pegasus/storage
chcon -R -t httpd_sys_rw_content_t /var/www/pegasus/bootstrap/cache

# Permitir conexiones de red para Apache
setsebool -P httpd_can_network_connect 1
setsebool -P httpd_can_network_connect_db 1
```

### 1️⃣9️⃣ Configurar Firewall

```bash
# Permitir tráfico HTTP y HTTPS
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload
```

### 2️⃣0️⃣ Configurar SSL (Opcional pero Recomendado)

```bash
# Instalar Certbot
dnf install -y certbot python3-certbot-apache

# Obtener certificado
certbot --apache -d tu-dominio.com -d www.tu-dominio.com
```

---

## 📦 Componentes Instalados

### PHP 8.3 + Extensiones

```
- php (CLI y Apache module)
- php-mysqlnd
- php-redis
- php-gd
- php-mbstring
- php-xml
- php-curl
- php-zip
- php-bcmath
- php-intl
- php-soap
- php-imagick
- php-opcache
```

### Servicios Configurados

| Servicio       | Puerto | Descripción        |
| -------------- | ------ | ------------------ |
| httpd (Apache) | 80/443 | Servidor web       |
| mysqld (MySQL) | 3306   | Base de datos      |
| redis          | 6379   | Cache y colas      |
| supervisord    | -      | Gestor de procesos |

### Supervisor Jobs

El script configura automáticamente:

1. **Laravel Queue Worker** (2 procesos)

    - Procesa las colas de trabajos
    - Auto-reinicio en caso de fallos
    - Logs en `storage/logs/worker.log`

2. **Laravel Scheduler**
    - Ejecuta cada minuto
    - Gestiona tareas programadas
    - Logs en `storage/logs/scheduler.log`

### Tareas Programadas (Scheduler)

| Hora     | Tarea                       | Descripción                    |
| -------- | --------------------------- | ------------------------------ |
| 08:00 AM | RenovarCobroPlacasJob       | Renueva placas vencidas        |
| 09:00 AM | CreateRecibosJob            | Genera recibos                 |
| 09:30 AM | NotifyVencimientoRecibosJob | Notifica próximos vencimientos |
| 09:30 AM | NotifyRecibosVencidosJob    | Notifica recibos vencidos      |

## ⚙️ Configuración Post-Instalación

### 1. Crear Usuario Administrador

```bash
cd /var/www/pegasus
php artisan tinker
```

En tinker:

```php
$user = new App\Models\User();
$user->name = 'Administrador';
$user->email = 'admin@tuempresa.com';
$user->password = bcrypt('TuPasswordSeguro123');
$user->save();
```

### 2. Configurar WhatsApp API

Editar `.env`:

```bash
vim /var/www/pegasus/.env
```

Agregar:

```env
WHATSAPP_API_URL=https://tu-api-whatsapp.com/send-message
WHATSAPP_API_KEY=tu-api-key
WHATSAPP_SENDER=51999999999
```

Limpiar cache:

```bash
php artisan config:cache
```

### 3. Configurar SMTP (Correos)

Editar `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tuempresa.com
MAIL_FROM_NAME="Pegasus GPS"
```

### 4. Configurar Acceso a phpMyAdmin

Por defecto, phpMyAdmin solo es accesible desde localhost. Para permitir tu IP:

```bash
vim /etc/apache2/conf-available/phpmyadmin.conf
```

Agregar tu IP:

```apache
<RequireAny>
    Require ip 127.0.0.1
    Require ip ::1
    Require ip TU.IP.PUBLICA.AQUI
</RequireAny>
```

Reiniciar Apache:

```bash
systemctl restart httpd
```

### 5. Configurar Backup Automático

Crear script de backup:

```bash
nano /root/backup-pegasus.sh
```

Contenido:

```bash
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/pegasus"
mkdir -p $BACKUP_DIR

# Backup MySQL
mysqldump -u root -p'TU_PASSWORD_MYSQL' pegasus > $BACKUP_DIR/db_$TIMESTAMP.sql

# Comprimir
gzip $BACKUP_DIR/db_$TIMESTAMP.sql

# Eliminar backups antiguos (más de 7 días)
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
```

Dar permisos:

```bash
chmod +x /root/backup-pegasus.sh
```

Agregar a crontab:

```bash
crontab -e
```

Agregar línea:

```
0 2 * * * /root/backup-pegasus.sh
```

## 🔧 Comandos Útiles

### Ver Estado de Servicios

```bash
# Estado de todos los servicios
systemctl status httpd
systemctl status mysqld
systemctl status redis
systemctl status supervisord

# Estado de Supervisor jobs
supervisorctl status
```

### Gestión de Colas

```bash
# Ver estado
supervisorctl status pegasus-queue:*

# Reiniciar workers
supervisorctl restart pegasus-queue:*

# Ver logs en tiempo real
supervisorctl tail -f pegasus-queue:pegasus-queue_00
```

### Gestión del Scheduler

```bash
# Ver estado
supervisorctl status pegasus-scheduler

# Ver logs
supervisorctl tail -f pegasus-scheduler

# Ejecutar manualmente
php /var/www/pegasus/artisan schedule:run
```

### Logs de Laravel

```bash
# Ver logs en tiempo real
tail -f /var/www/pegasus/storage/logs/laravel.log

# Ver últimas 100 líneas
tail -n 100 /var/www/pegasus/storage/logs/laravel.log

# Buscar errores
grep "ERROR" /var/www/pegasus/storage/logs/laravel.log
```

### Logs de Apache

```bash
# Ver logs de acceso
tail -f /var/log/httpd/pegasus-access.log

# Ver logs de errores
tail -f /var/log/httpd/pegasus-error.log
```

### Limpiar Cache

```bash
cd /var/www/pegasus

# Limpiar todo
php artisan optimize:clear

# Individual
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Ejecutar Migraciones

```bash
cd /var/www/pegasus

# Ver estado
php artisan migrate:status

# Ejecutar pendientes
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Recrear todo (¡CUIDADO! Elimina datos)
php artisan migrate:fresh
```

### Gestión de Renovación de Placas

```bash
# Ejecutar manualmente
php artisan cobros:renovar-placas --sync

# Ver ayuda
php artisan cobros:renovar-placas --help
```

## 🔒 Seguridad

### Firewall (FirewallD)

El script configura automáticamente FirewallD:

```bash
# Ver estado
firewall-cmd --state

# Ver servicios permitidos
firewall-cmd --list-services

# Permitir nuevo puerto
firewall-cmd --permanent --add-port=8080/tcp
firewall-cmd --reload

# Ver reglas
firewall-cmd --list-all
```

### SELinux

```bash
# Ver estado de SELinux
getenforce

# Ver contextos de archivos Laravel
ls -Z /var/www/pegasus/storage

# Si hay problemas de permisos, reconfigurar:
chcon -R -t httpd_sys_rw_content_t /var/www/pegasus/storage
chcon -R -t httpd_sys_rw_content_t /var/www/pegasus/bootstrap/cache
setsebool -P httpd_can_network_connect 1
setsebool -P httpd_can_network_connect_db 1
```

### Cambiar Contraseñas

#### MySQL Root:

```bash
mysql -u root -p
```

En MySQL:

```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'NuevaPasswordSegura123!';
FLUSH PRIVILEGES;
```

#### Usuario de Base de Datos:

```sql
ALTER USER 'pegasus'@'localhost' IDENTIFIED BY 'NuevaPasswordSegura123!';
FLUSH PRIVILEGES;
```

No olvides actualizar `.env`:

```bash
nano /var/www/pegasus/.env
# Cambiar DB_PASSWORD
php artisan config:cache
```

### Actualizar SSL

```bash
# Renovar manualmente
certbot renew

# Probar renovación
certbot renew --dry-run

# Auto-renovación está configurada vía systemd
systemctl list-timers | grep certbot
```

## 🐛 Solución de Problemas

### Error: "Queue connection does not exist"

```bash
# Verificar Redis
redis-cli ping
# Debe responder: PONG

# Verificar configuración
grep QUEUE_CONNECTION /var/www/pegasus/.env
# Debe ser: QUEUE_CONNECTION=redis

# Reiniciar workers
supervisorctl restart pegasus-queue:*
```

### Error: Permisos en storage/

```bash
cd /var/www/pegasus
chown -R apache:apache storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Configurar contexto SELinux
chcon -R -t httpd_sys_rw_content_t storage
chcon -R -t httpd_sys_rw_content_t bootstrap/cache
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"

```bash
# Verificar MySQL
systemctl status mysqld

# Iniciar si está detenido
systemctl start mysqld

# Ver logs
tail -f /var/log/mysqld.log
```

### Error: "403 Forbidden" en el navegador

```bash
# Verificar permisos
ls -la /var/www/pegasus/public

# Verificar contexto SELinux
ls -Z /var/www/pegasus/public

# Reconfigurar si es necesario
chcon -R -t httpd_sys_content_t /var/www/pegasus
chcon -R -t httpd_sys_rw_content_t /var/www/pegasus/storage
```

### Workers no procesan trabajos

```bash
# Ver logs
supervisorctl tail pegasus-queue:pegasus-queue_00

# Reiniciar
supervisorctl restart pegasus-queue:*

# Verificar queue
php artisan queue:work --once
```

### Error 500 en el sitio

```bash
# Ver logs de Apache
tail -f /var/log/httpd/pegasus-error.log

# Ver logs de Laravel
tail -f /var/www/pegasus/storage/logs/laravel.log

# Verificar permisos
ls -la /var/www/pegasus/storage

# Limpiar cache
php artisan optimize:clear
```

### Apache no inicia

```bash
# Verificar configuración
httpd -t

# Ver logs de error
tail -f /var/log/httpd/error_log

# Verificar puertos en uso
ss -tlnp | grep :80
ss -tlnp | grep :443
```

## 📊 Monitoreo

### Verificar Uso de Recursos

```bash
# CPU y Memoria
htop

# Espacio en disco
df -h

# Uso de MySQL
mysql -u root -p -e "SHOW PROCESSLIST;"

# Uso de Redis
redis-cli INFO memory
```

### Logs Importantes

| Log           | Ubicación                                     |
| ------------- | --------------------------------------------- |
| Laravel       | `/var/www/pegasus/storage/logs/laravel.log`   |
| Apache Error  | `/var/log/httpd/pegasus-error.log`            |
| Apache Access | `/var/log/httpd/pegasus-access.log`           |
| MySQL         | `/var/log/mysqld.log`                         |
| Supervisor    | `/var/log/supervisor/supervisord.log`         |
| Queue Worker  | `/var/www/pegasus/storage/logs/worker.log`    |
| Scheduler     | `/var/www/pegasus/storage/logs/scheduler.log` |

## 🔄 Actualizar el Sistema

```bash
cd /var/www/pegasus

# Hacer backup primero
/root/backup-pegasus.sh

# Obtener últimos cambios
git pull origin master

# Actualizar dependencias
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Ejecutar migraciones
php artisan migrate --force

# Limpiar cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar workers
sudo supervisorctl restart pegasus-worker:*
```

## 📞 Soporte

Si encuentras problemas durante la instalación:

1. Revisa los logs relevantes
2. Verifica que todos los servicios estén ejecutándose
3. Verifica que el dominio apunte correctamente al servidor
4. Consulta la documentación de Laravel: https://laravel.com/docs/12.x

---

**¡Importante!** Guarda las credenciales en un lugar seguro y mantén el sistema actualizado regularmente.
