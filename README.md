# 🛰️ Pegasus GPS - Sistema de Gestión de Recibos

Sistema de facturación y gestión de cobros para servicios GPS vehiculares. Gestiona clientes, servicios, cobros recurrentes por placas vehiculares, emisión automática de recibos con prorrateado, y notificaciones WhatsApp.

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![Livewire](https://img.shields.io/badge/Livewire-3-purple)
![License](https://img.shields.io/badge/License-MIT-green)

## 📋 Características Principales

-   ✅ **Gestión de Clientes** con múltiples teléfonos WhatsApp
-   📊 **Gestión de Servicios GPS** con precios base
-   🚗 **Cobros por Placas Vehiculares** con fechas de periodo
-   📄 **Generación Automática de Recibos** con prorrateado
-   🔄 **Renovación Automática** de placas para cobros recurrentes
-   📱 **Notificaciones WhatsApp** automáticas con PDF adjunto
-   💰 **Estados de Recibos**: pendiente, pagado, vencido, anulado
-   📈 **Reportes y Exportación** a Excel
-   🌐 **Recibos Públicos** vía URL única (UUID)
-   🔐 **Autenticación** con roles (Administrador, Usuario)
-   🎨 **Interfaz Moderna** con WireUI, Flux UI y Tailwind v4
-   🌙 **Dark Mode** incluido

## 🏗️ Stack Tecnológico

### Backend

-   **Laravel 12** - Framework PHP
-   **PHP 8.3** - Lenguaje
-   **MySQL 8.0** - Base de datos
-   **Redis** - Cache y colas
-   **Supervisor** - Gestión de procesos

### Frontend

-   **Livewire 3** - Componentes reactivos
-   **Livewire Volt** - Componentes de una sola página
-   **WireUI 2** - Componentes UI interactivos
-   **Flux UI** - Componentes UI adicionales
-   **Tailwind CSS v4** - Framework CSS
-   **Alpine.js** - JavaScript reactivo (incluido con Livewire)
-   **Chart.js** - Gráficos

### Herramientas

-   **Composer** - Gestor de dependencias PHP
-   **NPM** - Gestor de dependencias JavaScript
-   **Laravel Pint** - Formateador de código
-   **PHPUnit** - Testing

## 🚀 Instalación Rápida

### Opción 1: Script Automático (Recomendado)

Para instalación en servidor AlmaLinux 9.5 desde cero:

```bash
# 1. Clonar el repositorio
git clone https://github.com/Jhamnerx/pegasus
cd pegasus

# 2. Ejecutar script de instalación
sudo bash install-server.sh
```

El script instala y configura automáticamente:

-   PHP 8.3 + extensiones (vía Remi)
-   Apache (httpd)
-   MySQL 8.0
-   Redis
-   Supervisor (colas y scheduler)
-   SELinux (contextos correctos)
-   FirewallD (puertos HTTP/HTTPS)
-   Certbot (SSL)
-   phpMyAdmin
-   El sistema completo

📖 **[Ver guía completa de instalación](INSTALL.md)**

### Opción 2: Instalación Manual

#### Requisitos del Servidor

-   **Sistema:** AlmaLinux 9.5 / Rocky Linux 9 / RHEL 9
-   **PHP:** 8.3+
-   **Composer:** 2.x
-   **Node.js:** 20+
-   **MySQL:** 8.0+
-   **Redis:** 6.0+
-   **Apache:** 2.4+ (httpd)
-   **Supervisor:** Última versión

#### Pasos

```bash
# 1. Clonar repositorio
git clone https://github.com/Jhamnerx/pegasus.git
cd pegasus

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JavaScript
npm install

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=pegasus
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Ejecutar migraciones
php artisan migrate

# 7. Compilar assets
npm run build

# 8. Configurar colas (Supervisor)
# Ver INSTALL.md para configuración de Supervisor

# 9. Iniciar servidor de desarrollo
php artisan serve
```

## ⚙️ Configuración

### Variables de Entorno Importantes

```env
# WhatsApp API
WHATSAPP_API_URL=https://api-whatsapp.com/send-message
WHATSAPP_API_KEY=tu-api-key
WHATSAPP_SENDER=51999999999

# Días de alerta para recibos
ALERT_DAYS=7,3,1

# Configuración de colas
QUEUE_CONNECTION=redis
```

### Comandos Artisan Personalizados

```bash
# Renovar placas vencidas manualmente
php artisan cobros:renovar-placas --sync

# Ver lista de comandos
php artisan list
```

## 📅 Jobs Programados (Scheduler)

El sistema ejecuta automáticamente vía Supervisor:

| Hora     | Job                         | Descripción                           |
| -------- | --------------------------- | ------------------------------------- |
| 08:00 AM | RenovarCobroPlacasJob       | Renueva placas vencidas               |
| 09:00 AM | CreateRecibosJob            | Genera recibos para placas que vencen |
| 09:30 AM | NotifyVencimientoRecibosJob | Notifica recibos próximos a vencer    |
| 09:30 AM | NotifyRecibosVencidosJob    | Notifica recibos ya vencidos          |

## 🔧 Desarrollo

```bash
# Servidor de desarrollo completo (servidor + queue + vite)
composer run dev

# Tests
php artisan test
php artisan test --filter=TestName

# Formatear código (SIEMPRE antes de commit)
vendor/bin/pint --dirty

# Queue worker manual
php artisan queue:work

# Limpiar cache
php artisan optimize:clear
```

## 📊 Modelo de Datos

### Entidades Principales

```
Cliente
  ↓ hasMany
Cobro
  ↓ hasMany
CobroPlaca (con fechas inicio/fin y prorrateado)
  ↓ genera
Recibo
  ↓ hasMany
ReciboDetalle
```

### Flujo de Negocio

1. Se crea un **Cobro** para un **Cliente** con un **Servicio**
2. Se agregan **CobroPlacas** con fechas de periodo
3. El sistema calcula automáticamente el **prorrateado** si aplica
4. **RenovarCobroPlacasJob** crea nuevas placas cuando vencen
5. **CreateRecibosJob** genera **Recibos** 7 días antes del vencimiento
6. **Notificaciones WhatsApp** se envían automáticamente
7. El proceso se repite indefinidamente hasta marcar el cobro como "procesado"

## 🎨 Capturas de Pantalla

_(Agregar capturas de pantalla aquí)_

## 📖 Documentación

-   [Guía de Instalación Completa](INSTALL.md)
-   [Guía de Deployment en cPanel](DEPLOYMENT.md)
-   [Instrucciones para AI/Copilot](.github/copilot-instructions.md)

## 🧪 Testing

El proyecto usa **PHPUnit** (NO Pest):

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=TestClassName
php artisan test tests/Feature/ExampleTest.php

# Con coverage (requiere Xdebug)
php artisan test --coverage
```

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Ejecuta Pint** antes de hacer push (`vendor/bin/pint --dirty`)
5. Push a la rama (`git push origin feature/AmazingFeature`)
6. Abre un Pull Request

### Convenciones de Código

-   **PHP**: PSR-12 (formateado con Laravel Pint)
-   **Naming**:
    -   Campos DB: `snake_case`
    -   Métodos: `camelCase`
    -   Clases: `PascalCase`
-   **Fechas**: Formato español `d/m/Y`
-   **Estados**: En español ('Activo', 'Inactivo', 'pendiente', 'pagado')
-   **Validación**: Inline en Livewire components (NO Form Requests)

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 👥 Autores

-   **Jhampier Quillca** - [@Jhamnerx](https://github.com/Jhamnerx)

## 🙏 Agradecimientos

-   Laravel Framework
-   Livewire
-   WireUI
-   Flux UI
-   TailwindCSS
-   Comunidad Laravel Perú

## 📞 Soporte

Si tienes problemas o preguntas:

1. Revisa la [documentación de instalación](INSTALL.md)
2. Verifica los logs en `storage/logs/laravel.log`
3. Abre un [Issue](https://github.com/Jhamnerx/pegasus/issues)

---

Desarrollado con ❤️ en Perú
