# 🛠️ Archivos de Ayuda para Despliegue

Esta carpeta contiene archivos PHP que te ayudarán a configurar Laravel sin acceso a terminal.

## ⚠️ IMPORTANTE - SEGURIDAD

**ELIMINA TODOS ESTOS ARCHIVOS DESPUÉS DE USARLOS**

Estos archivos están diseñados solo para la instalación inicial y pueden ser un riesgo de seguridad si se dejan en el servidor.

## 📁 Archivos Disponibles

### 1. `generate_key.php`

**Uso:** Generar APP_KEY para Laravel
**Cuándo usar:** Durante la configuración inicial del archivo .env
**URL:** `https://tudominio.com/setup_helpers/generate_key.php`

### 2. `migrate.php`

**Uso:** Ejecutar migraciones de base de datos
**Cuándo usar:** Después de configurar la base de datos en .env
**URL:** `https://tudominio.com/setup_helpers/migrate.php`

### 3. `optimize.php`

**Uso:** Optimizar Laravel para producción
**Cuándo usar:** Después de la instalación y tras cada actualización
**URL:** `https://tudominio.com/setup_helpers/optimize.php`

### 4. `symlink.php`

**Uso:** Crear enlace simbólico de public_html hacia la carpeta public de Laravel
**Cuándo usar:** Para enlazar tu dominio principal a Laravel
**URL:** `https://tudominio.com/setup_helpers/symlink.php`

### 5. `diagnostico.php`

**Uso:** Verificar el estado del sistema y diagnosticar problemas
**Cuándo usar:** Para troubleshooting y verificación
**URL:** `https://tudominio.com/setup_helpers/diagnostico.php`

## 🚀 Orden de Ejecución Recomendado

1. **Primero:** Configurar `.env` manualmente
2. **Ejecutar:** `generate_key.php` → copiar APP_KEY al .env
3. **Ejecutar:** `migrate.php` → crear tablas de base de datos
4. **Ejecutar:** `symlink.php` → enlazar dominio a Laravel
5. **Ejecutar:** `optimize.php` → optimizar para producción
6. **Verificar:** `diagnostico.php` → comprobar que todo esté bien
7. **ELIMINAR:** Toda la carpeta `setup_helpers/`

## 🔒 Comando para Eliminar (si tienes terminal)

```bash
rm -rf setup_helpers/
```

## 🗂️ Eliminar desde File Manager

1. Seleccionar la carpeta `setup_helpers`
2. Clic derecho → Delete
3. Confirmar eliminación

## 📞 Soporte

Si encuentras problemas:

1. Ejecuta `diagnostico.php` para ver el estado del sistema
2. Revisa los logs en `storage/logs/laravel.log`
3. Contacta al soporte técnico de tu hosting si necesitas ayuda con Composer
