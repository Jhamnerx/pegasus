#!/bin/bash

################################################################################
# Pegasus GPS - Script de Verificación Post-Instalación
################################################################################
# 
# Este script verifica que todos los componentes estén funcionando correctamente
# después de la instalación.
#
# Uso: sudo bash verify-installation.sh
#
################################################################################

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

ERRORS=0
WARNINGS=0

echo "================================================================"
echo "  PEGASUS GPS - VERIFICACIÓN DE INSTALACIÓN"
echo "================================================================"
echo ""

################################################################################
# VERIFICAR SERVICIOS
################################################################################

print_info "Verificando servicios..."
echo ""

services=("mysql" "redis-server" "apache2" "supervisor")
for service in "${services[@]}"; do
    if systemctl is-active --quiet "$service"; then
        print_success "$service está ejecutándose"
    else
        print_error "$service NO está ejecutándose"
        ((ERRORS++))
    fi
done

echo ""

################################################################################
# VERIFICAR PHP
################################################################################

print_info "Verificando PHP..."
echo ""

if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n1 | cut -d" " -f2 | cut -d"." -f1,2)
    if [[ "$PHP_VERSION" == "8.3" ]]; then
        print_success "PHP 8.3 instalado correctamente"
    else
        print_warning "PHP versión $PHP_VERSION (se esperaba 8.3)"
        ((WARNINGS++))
    fi
else
    print_error "PHP no está instalado"
    ((ERRORS++))
fi

# Verificar extensiones críticas
php_extensions=("mysql" "redis" "gd" "mbstring" "xml" "curl" "zip")
for ext in "${php_extensions[@]}"; do
    if php -m | grep -q "^$ext$"; then
        print_success "Extensión PHP: $ext"
    else
        print_error "Extensión PHP faltante: $ext"
        ((ERRORS++))
    fi
done

echo ""

################################################################################
# VERIFICAR COMPOSER
################################################################################

print_info "Verificando Composer..."
echo ""

if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | cut -d" " -f3)
    print_success "Composer $COMPOSER_VERSION instalado"
else
    print_error "Composer no está instalado"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR NODE Y NPM
################################################################################

print_info "Verificando Node.js y NPM..."
echo ""

if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    print_success "Node.js $NODE_VERSION instalado"
else
    print_error "Node.js no está instalado"
    ((ERRORS++))
fi

if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm --version)
    print_success "NPM $NPM_VERSION instalado"
else
    print_error "NPM no está instalado"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR MYSQL
################################################################################

print_info "Verificando MySQL..."
echo ""

if systemctl is-active --quiet mysql; then
    MYSQL_VERSION=$(mysql --version | awk '{print $5}' | cut -d',' -f1)
    print_success "MySQL $MYSQL_VERSION ejecutándose"
    
    # Verificar si podemos conectar
    if mysql -u root -e "SELECT 1" &> /dev/null; then
        print_success "Conexión a MySQL exitosa"
    else
        print_warning "No se puede conectar a MySQL sin contraseña (esto es normal si ya se configuró)"
    fi
else
    print_error "MySQL no está ejecutándose"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR REDIS
################################################################################

print_info "Verificando Redis..."
echo ""

if systemctl is-active --quiet redis-server; then
    if redis-cli ping | grep -q "PONG"; then
        print_success "Redis responde correctamente"
    else
        print_error "Redis no responde"
        ((ERRORS++))
    fi
else
    print_error "Redis no está ejecutándose"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR APACHE
################################################################################

print_info "Verificando Apache..."
echo ""

if systemctl is-active --quiet apache2; then
    APACHE_VERSION=$(apache2 -v | head -n1 | cut -d"/" -f2 | cut -d" " -f1)
    print_success "Apache $APACHE_VERSION ejecutándose"
    
    # Verificar módulos importantes
    modules=("rewrite" "headers" "ssl")
    for mod in "${modules[@]}"; do
        if apache2ctl -M 2>/dev/null | grep -q "${mod}_module"; then
            print_success "Módulo Apache: $mod"
        else
            print_error "Módulo Apache faltante: $mod"
            ((ERRORS++))
        fi
    done
else
    print_error "Apache no está ejecutándose"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR DIRECTORIO DEL PROYECTO
################################################################################

print_info "Verificando instalación del proyecto..."
echo ""

INSTALL_DIR="/var/www/pegasus"

if [[ -d "$INSTALL_DIR" ]]; then
    print_success "Directorio del proyecto existe: $INSTALL_DIR"
    
    # Verificar archivos críticos
    critical_files=(".env" "artisan" "composer.json" "package.json")
    for file in "${critical_files[@]}"; do
        if [[ -f "$INSTALL_DIR/$file" ]]; then
            print_success "Archivo: $file"
        else
            print_error "Archivo faltante: $file"
            ((ERRORS++))
        fi
    done
    
    # Verificar directorios con permisos
    writable_dirs=("storage" "bootstrap/cache")
    for dir in "${writable_dirs[@]}"; do
        if [[ -w "$INSTALL_DIR/$dir" ]]; then
            print_success "Directorio escribible: $dir"
        else
            print_error "Directorio NO escribible: $dir"
            ((ERRORS++))
        fi
    done
    
else
    print_error "Directorio del proyecto no existe: $INSTALL_DIR"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR SUPERVISOR
################################################################################

print_info "Verificando Supervisor..."
echo ""

if systemctl is-active --quiet supervisor; then
    print_success "Supervisor está ejecutándose"
    
    # Verificar jobs de Pegasus
    echo ""
    print_info "Estado de jobs de Supervisor:"
    supervisorctl status | grep pegasus
    
    # Contar procesos en ejecución
    RUNNING_JOBS=$(supervisorctl status | grep pegasus | grep RUNNING | wc -l)
    if [[ $RUNNING_JOBS -ge 2 ]]; then
        print_success "$RUNNING_JOBS jobs de Pegasus ejecutándose"
    else
        print_warning "Solo $RUNNING_JOBS jobs ejecutándose (se esperaban al menos 2)"
        ((WARNINGS++))
    fi
else
    print_error "Supervisor no está ejecutándose"
    ((ERRORS++))
fi

echo ""

################################################################################
# VERIFICAR FIREWALL
################################################################################

print_info "Verificando firewall..."
echo ""

if command -v ufw &> /dev/null; then
    UFW_STATUS=$(ufw status | grep "Status:" | awk '{print $2}')
    if [[ "$UFW_STATUS" == "active" ]]; then
        print_success "UFW está activo"
        
        # Verificar reglas importantes
        if ufw status | grep -q "Apache"; then
            print_success "Regla UFW: Apache configurado"
        else
            print_warning "Regla UFW: Apache no configurado"
            ((WARNINGS++))
        fi
    else
        print_warning "UFW está inactivo"
        ((WARNINGS++))
    fi
else
    print_warning "UFW no está instalado"
    ((WARNINGS++))
fi

echo ""

################################################################################
# VERIFICAR CONFIGURACIÓN DE LARAVEL
################################################################################

print_info "Verificando configuración de Laravel..."
echo ""

if [[ -f "$INSTALL_DIR/.env" ]]; then
    # Verificar APP_KEY
    if grep -q "^APP_KEY=base64:" "$INSTALL_DIR/.env"; then
        print_success "APP_KEY configurado"
    else
        print_error "APP_KEY no está configurado"
        ((ERRORS++))
    fi
    
    # Verificar APP_ENV
    APP_ENV=$(grep "^APP_ENV=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    if [[ "$APP_ENV" == "production" ]]; then
        print_success "APP_ENV=production"
    else
        print_warning "APP_ENV=$APP_ENV (se recomienda 'production')"
        ((WARNINGS++))
    fi
    
    # Verificar APP_DEBUG
    APP_DEBUG=$(grep "^APP_DEBUG=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    if [[ "$APP_DEBUG" == "false" ]]; then
        print_success "APP_DEBUG=false (correcto para producción)"
    else
        print_warning "APP_DEBUG=$APP_DEBUG (debe ser 'false' en producción)"
        ((WARNINGS++))
    fi
    
    # Verificar DB_CONNECTION
    if grep -q "^DB_CONNECTION=mysql" "$INSTALL_DIR/.env"; then
        print_success "DB_CONNECTION configurado"
    else
        print_error "DB_CONNECTION no está configurado"
        ((ERRORS++))
    fi
    
    # Verificar QUEUE_CONNECTION
    QUEUE_CONN=$(grep "^QUEUE_CONNECTION=" "$INSTALL_DIR/.env" | cut -d'=' -f2)
    if [[ "$QUEUE_CONN" == "redis" ]]; then
        print_success "QUEUE_CONNECTION=redis"
    else
        print_warning "QUEUE_CONNECTION=$QUEUE_CONN (se recomienda 'redis')"
        ((WARNINGS++))
    fi
fi

echo ""

################################################################################
# VERIFICAR LOGS
################################################################################

print_info "Verificando logs recientes..."
echo ""

LARAVEL_LOG="$INSTALL_DIR/storage/logs/laravel.log"
if [[ -f "$LARAVEL_LOG" ]]; then
    ERROR_COUNT=$(grep -c "ERROR" "$LARAVEL_LOG" 2>/dev/null || echo 0)
    if [[ $ERROR_COUNT -gt 0 ]]; then
        print_warning "$ERROR_COUNT errores encontrados en el log de Laravel"
        echo "    Últimos errores:"
        tail -n 5 "$LARAVEL_LOG" | grep "ERROR" | sed 's/^/    /'
        ((WARNINGS++))
    else
        print_success "Sin errores en el log de Laravel"
    fi
else
    print_warning "Log de Laravel no existe aún"
fi

echo ""

################################################################################
# VERIFICAR CONECTIVIDAD WEB
################################################################################

print_info "Verificando conectividad web..."
echo ""

# Verificar si Apache responde en localhost
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302\|301"; then
    print_success "Apache responde en localhost"
else
    print_warning "Apache no responde en localhost"
    ((WARNINGS++))
fi

echo ""

################################################################################
# RESUMEN
################################################################################

echo "================================================================"
echo "  RESUMEN DE VERIFICACIÓN"
echo "================================================================"
echo ""

if [[ $ERRORS -eq 0 && $WARNINGS -eq 0 ]]; then
    print_success "¡Todo está funcionando correctamente!"
    echo ""
    print_info "Próximos pasos:"
    echo "  1. Acceder al sistema vía web"
    echo "  2. Crear usuario administrador"
    echo "  3. Configurar WhatsApp API"
    exit 0
elif [[ $ERRORS -eq 0 ]]; then
    print_warning "Instalación completa con $WARNINGS advertencias"
    echo ""
    print_info "Revisa las advertencias y corrige si es necesario"
    exit 0
else
    print_error "Se encontraron $ERRORS errores y $WARNINGS advertencias"
    echo ""
    print_error "Por favor, corrige los errores antes de continuar"
    exit 1
fi
