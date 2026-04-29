#!/bin/sh
set -e

# =============================================================================
# UGR Portfolio — Script de inicialización del contenedor
# Se ejecuta una vez al arrancar, antes de lanzar Supervisor
# =============================================================================

echo "==> Iniciando UGR Portfolio..."

# 1. Generar APP_KEY si no está definida
if [ -z "$APP_KEY" ]; then
    echo "==> Generando APP_KEY..."
    php artisan key:generate --force
fi

# 2. Crear la base de datos SQLite si no existe
# (se usa solo para la tabla de usuarios; los datos de OpenProject van en JSON)
if [ ! -f "$DB_DATABASE" ]; then
    echo "==> Creando base de datos SQLite..."
    touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE"
fi

# 3. Ejecutar migraciones
echo "==> Ejecutando migraciones..."
php artisan migrate --force --no-interaction

# 4. Optimizar para producción
echo "==> Optimizando..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Asegurar permisos de storage
chown -R www-data:www-data storage bootstrap/cache

echo "==> Listo. Arrancando servicios..."
exec "$@"