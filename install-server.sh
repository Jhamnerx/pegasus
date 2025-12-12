#!/bin/bash

################################################################################
# Pegasus GPS - Script de Instalación Completa para AlmaLinux 9.5
################################################################################
# 
# Este script instala y configura:
# - PHP 8.3 + Extensiones
# - Apache (httpd) + mod_rewrite
# - MySQL 8.0
# - Redis
# - Supervisor (para colas y scheduler)
# - Certbot (SSL Let's Encrypt)
# - phpMyAdmin
# - Composer & NPM
# - Configura el sistema Pegasus GPS
#
# Requisitos: AlmaLinux 9.5 / Rocky Linux 9 / RHEL 9
# Ejecutar como root: sudo bash install-server.sh
#
# IMPORTANTE: Este script debe ejecutarse DENTRO del repositorio clonado
# 
# Pasos previos:
#   git clone https://github.com/Jhamnerx/pegasus
#   cd pegasus
#   sudo bash install-server.sh
#
################################################################################

set -e  # Detener si hay errores

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables de configuración
PROJECT_NAME="pegasus"
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSTALL_DIR="/var/www/pegasus"
CREDENTIALS_FILE="/root/CREDENCIALES.txt"
DOMAIN=""  # Se pedirá al usuario

# Función para imprimir mensajes
print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

# Función para generar contraseñas seguras
generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
}

################################################################################
# 1. VERIFICACIONES INICIALES
################################################################################

clear
echo "================================================================"
echo "  PEGASUS GPS - INSTALACIÓN AUTOMÁTICA DEL SERVIDOR"
echo "  AlmaLinux 9.5"
echo "================================================================"
echo ""

# Verificar si se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   print_error "Este script debe ejecutarse como root (sudo)"
   exit 1
fi

# Verificar que estamos en el directorio del repo
if [[ ! -f "$CURRENT_DIR/composer.json" ]] || [[ ! -f "$CURRENT_DIR/artisan" ]]; then
    print_error "Este script debe ejecutarse desde el directorio raíz del repositorio clonado"
    echo ""
    echo "Pasos correctos:"
    echo "  1. git clone https://github.com/Jhamnerx/pegasus"
    echo "  2. cd pegasus"
    echo "  3. sudo bash install-server.sh"
    exit 1
fi

print_info "Verificando sistema operativo..."
if [[ -f /etc/os-release ]]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
    print_success "Sistema: $OS $VER"
    
    # Verificar que sea AlmaLinux/Rocky/RHEL 9
    if [[ "$ID" != "almalinux" && "$ID" != "rocky" && "$ID" != "rhel" ]]; then
        print_warning "Este script está optimizado para AlmaLinux 9.5"
        print_warning "Sistema detectado: $ID"
        read -p "¿Continuar de todas formas? (y/n): " CONTINUE
        if [[ "$CONTINUE" != "y" && "$CONTINUE" != "Y" ]]; then
            exit 1
        fi
    fi
else
    print_error "No se puede determinar el sistema operativo"
    exit 1
fi

# Solicitar dominio
echo ""
read -p "Ingrese el dominio para el sistema (ej: pegasus.tudominio.com): " DOMAIN
if [[ -z "$DOMAIN" ]]; then
    print_error "El dominio es obligatorio"
    exit 1
fi

read -p "¿Desea configurar SSL con Certbot? (y/n): " CONFIGURE_SSL
CONFIGURE_SSL=${CONFIGURE_SSL:-n}

echo ""
print_info "Configuración:"
print_info "  - Dominio: $DOMAIN"
print_info "  - Directorio: $INSTALL_DIR"
print_info "  - SSL: $CONFIGURE_SSL"
echo ""
read -p "¿Continuar con la instalación? (y/n): " CONFIRM
if [[ "$CONFIRM" != "y" && "$CONFIRM" != "Y" ]]; then
    print_warning "Instalación cancelada"
    exit 0
fi

# Generar contraseñas
MYSQL_ROOT_PASSWORD=$(generate_password)
DB_NAME="pegasus"
DB_USER="root"
DB_PASSWORD="${MYSQL_ROOT_PASSWORD}"  # Misma contraseña para root
PHPMYADMIN_SECRET=$(generate_password)

# 2. ACTUALIZAR SISTEMA
################################################################################

print_info "Actualizando sistema..."
dnf update -y
print_success "Sistema actualizado"

################################################################################
# 3. INSTALAR DEPENDENCIAS BÁSICAS Y REPOSITORIOS
################################################################################

print_info "Instalando EPEL release..."
dnf install -y epel-release

print_info "Instalando dependencias básicas..."
dnf install -y \
    dnf-plugins-core \
    curl \
    wget \
    git \
    unzip \
    tar \
    vim \
    policycoreutils-python-utils

# Instalar htop desde EPEL (opcional)
dnf install -y htop 2>/dev/null || print_warning "htop no disponible, continuando..."

print_success "Dependencias básicas instaladas"

# Habilitar CodeReady Builder (necesario para algunas dependencias)
print_info "Habilitando CodeReady Builder..."
dnf config-manager --set-enabled crb -y || \
    dnf config-manager --set-enabled powertools -y
print_success "CodeReady Builder habilitado"

################################################################################
# 4. INSTALAR REPOSITORIO REMI PARA PHP 8.3
################################################################################

print_info "Instalando repositorio Remi..."
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
dnf config-manager --set-enabled remi -y
print_success "Repositorio Remi instalado"

################################################################################
# 5. INSTALAR PHP 8.3
################################################################################

print_info "Instalando PHP 8.3 y extensiones..."
dnf module reset php -y
dnf module install -y php:remi-8.3

dnf install -y \
    php \
    php-cli \
    php-fpm \
    php-common \
    php-mysqlnd \
    php-zip \
    php-gd \
    php-mbstring \
    php-curl \
    php-xml \
    php-bcmath \
    php-intl \
    php-redis \
    php-soap \
    php-imagick \
    php-opcache \
    php-json \
    php-process \
    php-pdo

# Configurar PHP
print_info "Configurando PHP..."
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 100M/" /etc/php.ini
sed -i "s/post_max_size = .*/post_max_size = 100M/" /etc/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php.ini
sed -i "s/max_execution_time = .*/max_execution_time = 300/" /etc/php.ini
sed -i "s/;date.timezone =.*/date.timezone = America\/Lima/" /etc/php.ini

print_success "PHP 8.3 instalado y configurado"

################################################################################
# 6. INSTALAR COMPOSER
################################################################################

print_info "Instalando Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer
print_success "Composer instalado: $(composer --version | head -n1)"

################################################################################
# 7. INSTALAR NODE.JS Y NPM
################################################################################

print_info "Instalando Node.js 20.x y NPM..."
curl -fsSL https://rpm.nodesource.com/setup_20.x | bash -
dnf install -y nodejs
npm install -g npm@latest
print_success "Node.js $(node --version) y NPM $(npm --version) instalados"

################################################################################
# 8. INSTALAR Y CONFIGURAR MYSQL
################################################################################

print_info "Instalando MySQL 8.0..."
dnf install -y mysql-server

# Configurar MySQL de forma segura
print_info "Configurando MySQL..."
systemctl start mysqld
systemctl enable mysqld

# Verificar si MySQL ya tiene contraseña configurada
print_info "Verificando configuración de MySQL..."
MYSQL_CONFIGURED=false

# Intentar conectar sin contraseña
if mysql -u root -e "SELECT 1;" 2>/dev/null; then
    print_info "MySQL sin contraseña, configurando..."
    # Configurar contraseña root
    mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PASSWORD}';
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    MYSQL_CONFIGURED=true
    print_success "Contraseña root configurada"
elif mysql -u root -p"${MYSQL_ROOT_PASSWORD}" -e "SELECT 1;" 2>/dev/null; then
    print_info "MySQL ya tiene contraseña configurada (reutilizando)"
    MYSQL_CONFIGURED=true
else
    print_warning "No se pudo conectar a MySQL con las credenciales esperadas"
    print_warning "Puede que MySQL ya tenga una contraseña diferente"
    read -p "¿Conoces la contraseña actual de root? (y/n): " KNOW_PASSWORD
    if [[ "$KNOW_PASSWORD" == "y" || "$KNOW_PASSWORD" == "Y" ]]; then
        read -sp "Ingresa la contraseña actual de root: " CURRENT_MYSQL_PASSWORD
        echo ""
        if mysql -u root -p"${CURRENT_MYSQL_PASSWORD}" -e "SELECT 1;" 2>/dev/null; then
            MYSQL_ROOT_PASSWORD="$CURRENT_MYSQL_PASSWORD"
            DB_PASSWORD="$CURRENT_MYSQL_PASSWORD"  # Actualizar DB_PASSWORD también
            MYSQL_CONFIGURED=true
            print_success "Conectado con contraseña existente"
        else
            print_error "Contraseña incorrecta"
            exit 1
        fi
    else
        print_error "No se puede continuar sin acceso a MySQL"
        exit 1
    fi
fi

# Crear base de datos y usuario
print_info "Creando base de datos..."
mysql -u root -p"${MYSQL_ROOT_PASSWORD}" <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
FLUSH PRIVILEGES;
EOF

print_success "MySQL configurado y base de datos creada"

################################################################################
# 9. INSTALAR REDIS
################################################################################

print_info "Instalando Redis..."
dnf install -y redis
systemctl enable redis
systemctl start redis
print_success "Redis instalado y ejecutándose"

################################################################################
# 10. INSTALAR Y CONFIGURAR APACHE (HTTPD)
################################################################################

print_info "Instalando Apache (httpd)..."
dnf install -y httpd mod_ssl

systemctl enable httpd
print_success "Apache instalado"

################################################################################
# 11. COPIAR REPOSITORIO AL DIRECTORIO FINAL
################################################################################

# Verificar si ya estamos en el directorio de instalación
if [[ "$CURRENT_DIR" == "$INSTALL_DIR" ]]; then
    print_info "El repositorio ya está en $INSTALL_DIR"
    print_success "Usando repositorio existente"
else
    print_info "Copiando repositorio a $INSTALL_DIR..."
    
    # Crear directorio padre si no existe
    mkdir -p "$(dirname "$INSTALL_DIR")"
    
    # Si el directorio destino existe, hacer backup
    if [[ -d "$INSTALL_DIR" ]]; then
        print_warning "Directorio $INSTALL_DIR existe, creando backup..."
        mv "$INSTALL_DIR" "${INSTALL_DIR}.backup.$(date +%Y%m%d_%H%M%S)"
    fi
    
    # Copiar todo el directorio actual al destino
    cp -r "$CURRENT_DIR" "$INSTALL_DIR"
    print_success "Repositorio copiado"
fi

cd "$INSTALL_DIR"

################################################################################
# 12. INSTALAR DEPENDENCIAS DEL PROYECTO
################################################################################

# Verificar que composer esté disponible
print_info "Verificando Composer..."
if ! command -v composer &> /dev/null; then
    print_warning "Composer no encontrado en PATH, buscando..."
    if [[ -f /usr/local/bin/composer ]]; then
        export PATH="/usr/local/bin:$PATH"
        hash -r  # Rehash del PATH
        print_success "Composer encontrado en /usr/local/bin"
    else
        print_error "Composer no está instalado. Instalando ahora..."
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        chmod +x /usr/local/bin/composer
        export PATH="/usr/local/bin:$PATH"
        hash -r
    fi
fi

print_info "Instalando dependencias de Composer (esto puede tomar varios minutos)..."
/usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
print_success "Dependencias de Composer instaladas"

print_info "Instalando dependencias de NPM (esto puede tomar varios minutos)..."
/usr/bin/npm install
print_success "Dependencias de NPM instaladas"

print_info "Compilando assets..."
/usr/bin/npm run build
print_success "Assets compilados"

################################################################################
# 13. CONFIGURAR LARAVEL
################################################################################

print_info "Configurando Laravel..."

# Copiar .env
if [[ ! -f .env ]]; then
    cp .env.example .env
fi

# Generar APP_KEY
php artisan key:generate --force > /dev/null 2>&1

# Configurar .env
APP_KEY=$(grep APP_KEY .env | cut -d '=' -f2)
cat > .env <<EOF
APP_NAME="Pegasus GPS"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_TIMEZONE=America/Lima
APP_URL=https://${DOMAIN}
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_PE

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

# WhatsApp API (Configurar después)
WHATSAPP_API_URL=
WHATSAPP_API_KEY=
WHATSAPP_SENDER=

# Días de alerta para recibos
ALERT_DAYS=7,3,1
EOF

# Ejecutar migraciones
print_info "Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders si existen
if [[ -f database/seeders/DatabaseSeeder.php ]]; then
    print_info "Ejecutando seeders..."
    php artisan db:seed --force || true
fi

# Optimizar Laravel
print_info "Optimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Establecer permisos
print_info "Configurando permisos..."
chown -R apache:apache "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/bootstrap/cache"

print_success "Laravel configurado"

################################################################################
# 14. CONFIGURAR VIRTUALHOST DE APACHE
################################################################################

print_info "Configurando VirtualHost de Apache..."

cat > "/etc/httpd/conf.d/${PROJECT_NAME}.conf" <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAdmin admin@${DOMAIN}
    DocumentRoot ${INSTALL_DIR}/public

    <Directory ${INSTALL_DIR}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory ${INSTALL_DIR}>
        Options -Indexes
    </Directory>

    ErrorLog /var/log/httpd/${PROJECT_NAME}-error.log
    CustomLog /var/log/httpd/${PROJECT_NAME}-access.log combined

    # Security Headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
EOF

# Deshabilitar default welcome page
mv /etc/httpd/conf.d/welcome.conf /etc/httpd/conf.d/welcome.conf.bak 2>/dev/null || true

systemctl restart httpd
print_success "VirtualHost configurado"

################################################################################
# 15. CONFIGURAR SUPERVISOR
################################################################################

print_info "Instalando y configurando Supervisor..."
dnf install -y supervisor

# Configuración para Laravel Queue Worker
cat > "/etc/supervisord.d/${PROJECT_NAME}-worker.ini" <<EOF
[program:${PROJECT_NAME}-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${INSTALL_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=apache
numprocs=2
redirect_stderr=true
stdout_logfile=${INSTALL_DIR}/storage/logs/worker.log
stopwaitsecs=3600
EOF

# Configuración para Laravel Scheduler
cat > "/etc/supervisord.d/${PROJECT_NAME}-scheduler.ini" <<EOF
[program:${PROJECT_NAME}-scheduler]
process_name=%(program_name)s
command=/bin/sh -c "while [ true ]; do (php ${INSTALL_DIR}/artisan schedule:run --verbose --no-interaction &); sleep 60; done"
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=apache
numprocs=1
redirect_stderr=true
stdout_logfile=${INSTALL_DIR}/storage/logs/scheduler.log
EOF

systemctl enable supervisord
systemctl restart supervisord
supervisorctl reread
supervisorctl update
supervisorctl start all

print_success "Supervisor configurado y ejecutándose"

################################################################################
# 16. INSTALAR PHPMYADMIN
################################################################################

print_info "Instalando phpMyAdmin..."

# Descargar phpMyAdmin
PHPMYADMIN_VERSION="5.2.1"
cd /tmp
wget -q https://files.phpmyadmin.net/phpMyAdmin/${PHPMYADMIN_VERSION}/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.tar.gz
tar -xzf phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.tar.gz
mv phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages /usr/share/phpmyadmin
rm -f phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.tar.gz

# Crear directorio de configuración
mkdir -p /usr/share/phpmyadmin/tmp
chmod 777 /usr/share/phpmyadmin/tmp
mkdir -p /var/lib/phpmyadmin/tmp
chown -R apache:apache /var/lib/phpmyadmin

# Configurar phpMyAdmin
cp /usr/share/phpmyadmin/config.sample.inc.php /usr/share/phpmyadmin/config.inc.php
BLOWFISH_SECRET=$(openssl rand -base64 32)
sed -i "s/\$cfg\['blowfish_secret'\] = '';/\$cfg['blowfish_secret'] = '${BLOWFISH_SECRET}';/" /usr/share/phpmyadmin/config.inc.php
echo "\$cfg['TempDir'] = '/usr/share/phpmyadmin/tmp';" >> /usr/share/phpmyadmin/config.inc.php

# Configurar acceso seguro a phpMyAdmin
PHPMYADMIN_ALIAS="/pma-$(openssl rand -hex 8)"
cat > /etc/httpd/conf.d/phpMyAdmin.conf <<EOF
Alias ${PHPMYADMIN_ALIAS} /usr/share/phpmyadmin

<Directory /usr/share/phpmyadmin>
    Options FollowSymLinks
    DirectoryIndex index.php
    AllowOverride All
    
    <RequireAny>
        Require ip 127.0.0.1
        Require ip ::1
        # Añadir aquí IPs permitidas
    </RequireAny>
</Directory>

<Directory /usr/share/phpmyadmin/templates>
    Require all denied
</Directory>

<Directory /usr/share/phpmyadmin/libraries>
    Require all denied
</Directory>

<Directory /usr/share/phpmyadmin/setup>
    Require all denied
</Directory>
EOF

systemctl restart httpd

print_success "phpMyAdmin instalado en: https://${DOMAIN}${PHPMYADMIN_ALIAS}"

################################################################################
# 17. CONFIGURAR SSL CON CERTBOT
################################################################################

# Instalar Certbot para AlmaLinux
print_info "Instalando Certbot..."
dnf install -y certbot python3-certbot-apache
print_success "Certbot instalado"

if [[ "$CONFIGURE_SSL" == "y" || "$CONFIGURE_SSL" == "Y" ]]; then
    print_info "Configurando SSL con Let's Encrypt..."
    
    # Verificar que el dominio apunte al servidor
    print_warning "Asegúrate de que el dominio ${DOMAIN} apunte a este servidor"
    read -p "¿El dominio ya está configurado en el DNS? (y/n): " DNS_READY
    
    if [[ "$DNS_READY" == "y" || "$DNS_READY" == "Y" ]]; then
        read -p "Ingresa un email para las notificaciones de SSL: " SSL_EMAIL
        
        certbot --apache \
            --non-interactive \
            --agree-tos \
            --email "$SSL_EMAIL" \
            -d "$DOMAIN" \
            --redirect
        
        print_success "SSL configurado exitosamente"
    else
        print_warning "SSL omitido. Ejecuta después: sudo certbot --apache -d ${DOMAIN}"
    fi
fi

################################################################################
# 18. CONFIGURAR SELINUX
################################################################################

print_info "Configurando SELinux..."

# Permitir a httpd conectarse a la red (para Redis, MySQL)
setsebool -P httpd_can_network_connect 1
setsebool -P httpd_can_network_connect_db 1

# Configurar contextos de SELinux para Laravel
chcon -R -t httpd_sys_rw_content_t ${INSTALL_DIR}/storage
chcon -R -t httpd_sys_rw_content_t ${INSTALL_DIR}/bootstrap/cache

print_success "SELinux configurado"

################################################################################
# 19. CONFIGURAR FIREWALL (FIREWALLD)
################################################################################

print_info "Configurando firewall FirewallD..."
systemctl start firewalld
systemctl enable firewalld

firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --permanent --add-service=ssh
firewall-cmd --reload

print_success "Firewall configurado"

################################################################################
# 20. CREAR ARCHIVO DE CREDENCIALES
################################################################################

print_info "Generando archivo de credenciales..."

cat > "$CREDENTIALS_FILE" <<EOF
================================================================================
PEGASUS GPS - CREDENCIALES DEL SISTEMA
================================================================================
Generado: $(date)
Servidor: $(hostname)
IP: $(hostname -I | awk '{print $1}')

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ACCESO AL SISTEMA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
URL:            https://${DOMAIN}
Directorio:     ${INSTALL_DIR}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

MYSQL
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Host:           localhost
Puerto:         3306
Usuario:        root
Password:       ${MYSQL_ROOT_PASSWORD}
Base de Datos:  ${DB_NAME}

NOTA: Se usa el usuario root para la aplicación y phpMyAdmin

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

PHPMYADMIN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
URL:            https://${DOMAIN}${PHPMYADMIN_ALIAS}
Usuario:        root
Password:       ${MYSQL_ROOT_PASSWORD}

NOTA: Solo accesible desde IPs autorizadas (configurar en /etc/apache2/conf-available/phpmyadmin.conf)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

REDIS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Host:           127.0.0.1
Puerto:         6379
Password:       (sin password)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SUPERVISOR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Estado:         sudo supervisorctl status
Reiniciar:      sudo supervisorctl restart all
Ver logs:       sudo supervisorctl tail -f ${PROJECT_NAME}-worker
                sudo supervisorctl tail -f ${PROJECT_NAME}-scheduler

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

COMANDOS ÚTILES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Logs Laravel:       tail -f ${INSTALL_DIR}/storage/logs/laravel.log
Logs Apache:        tail -f /var/log/httpd/${PROJECT_NAME}-error.log
Limpiar cache:      php ${INSTALL_DIR}/artisan optimize:clear
Ver colas:          php ${INSTALL_DIR}/artisan queue:work --once
Ejecutar scheduler: php ${INSTALL_DIR}/artisan schedule:run
Renovar placas:     php ${INSTALL_DIR}/artisan cobros:renovar-placas --sync

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FIREWALL (FIREWALLD)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Ver servicios:      firewall-cmd --list-services
Agregar IP:         firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="TU.IP.AQUI" accept'
Recargar:           firewall-cmd --reload

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SELINUX
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Estado:             getenforce
Ver contextos:      ls -Z ${INSTALL_DIR}/storage
Permisos storage:   chcon -R -t httpd_sys_rw_content_t ${INSTALL_DIR}/storage

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

TAREAS PROGRAMADAS (Configuradas en Supervisor)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✓ Laravel Queue Worker (2 procesos)
✓ Laravel Scheduler (cada minuto)

Jobs programados:
- 08:00 AM: Renovar placas vencidas
- 09:00 AM: Crear recibos
- 09:30 AM: Notificar vencimientos

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CONFIGURACIÓN PENDIENTE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Configurar WhatsApp API en ${INSTALL_DIR}/.env
   - WHATSAPP_API_URL
   - WHATSAPP_API_KEY
   - WHATSAPP_SENDER

2. Configurar SMTP para emails en ${INSTALL_DIR}/.env

3. Crear primer usuario administrador:
   cd ${INSTALL_DIR}
   php artisan tinker
   >>> \$user = new App\Models\User();
   >>> \$user->name = 'Administrador';
   >>> \$user->email = 'admin@example.com';
   >>> \$user->password = bcrypt('password123');
   >>> \$user->save();

4. Configurar IPs permitidas para phpMyAdmin:
   vim /etc/apache2/conf-available/phpmyadmin.conf

5. Configurar backup automático de base de datos

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SEGURIDAD
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠ IMPORTANTE:
1. Cambia las contraseñas después de la primera instalación
2. Configura backup automático de la base de datos
3. Mantén el sistema actualizado: apt-get update && apt-get upgrade
4. Revisa logs regularmente
5. Guarda este archivo en un lugar seguro y elimínalo del servidor

EOF

chmod 600 "$CREDENTIALS_FILE"
print_success "Credenciales guardadas en: $CREDENTIALS_FILE"

################################################################################
# 21. VERIFICACIONES FINALES
################################################################################

print_info "Ejecutando verificaciones finales..."

# Verificar servicios
services_ok=true
for service in mysqld redis httpd supervisord; do
    if systemctl is-active --quiet $service; then
        print_success "$service está ejecutándose"
    else
        print_error "$service NO está ejecutándose"
        services_ok=false
    fi
done

# Verificar supervisor jobs
print_info "Estado de Supervisor:"
supervisorctl status

# Verificar permisos
if [[ -w "${INSTALL_DIR}/storage" && -w "${INSTALL_DIR}/bootstrap/cache" ]]; then
    print_success "Permisos correctos en storage y cache"
else
    print_error "Problemas con permisos en storage o cache"
fi

################################################################################
# 22. RESUMEN FINAL
################################################################################

clear
echo ""
echo "================================================================"
echo "  ✓ INSTALACIÓN COMPLETADA EXITOSAMENTE"
echo "================================================================"
echo ""
print_success "Pegasus GPS está listo para usar"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  INFORMACIÓN DE ACCESO"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  🌐 Sistema:     https://${DOMAIN}"
echo "  📊 phpMyAdmin:  https://${DOMAIN}${PHPMYADMIN_ALIAS}"
echo "  📄 Credenciales: ${CREDENTIALS_FILE}"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  PRÓXIMOS PASOS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  1. Ver credenciales:"
echo "     cat ${CREDENTIALS_FILE}"
echo ""
echo "  2. Crear usuario administrador"
echo ""
echo "  3. Configurar WhatsApp API en .env"
echo ""
echo "  4. Configurar backup de base de datos"
echo ""
echo "  5. Verificar que las colas estén funcionando:"
echo "     sudo supervisorctl status"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

print_warning "¡IMPORTANTE! Guarda el archivo de credenciales en un lugar seguro:"
echo "             ${CREDENTIALS_FILE}"
echo ""

if [[ "$services_ok" == true ]]; then
    print_success "Todos los servicios están ejecutándose correctamente"
else
    print_error "Algunos servicios tienen problemas. Revisa los logs."
fi

echo ""
echo "================================================================"
