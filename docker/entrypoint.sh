#!/bin/sh
set -e

echo "==> Iniciando UGR Portfolio..."

# 1. Crear .env desde variables de entorno del contenedor
# Siempre se regenera para reflejar los valores actuales del compose
echo "==> Configurando entorno..."
printf "APP_NAME=\"UGR Portfolio\"\n"                              > /var/www/html/.env
printf "APP_ENV=production\n"                                      >> /var/www/html/.env
printf "APP_DEBUG=false\n"                                         >> /var/www/html/.env
printf "APP_URL=%s\n"           "${APP_URL:-http://localhost/portfolio}" >> /var/www/html/.env
printf "APP_LOCALE=es\n"                                           >> /var/www/html/.env
printf "DB_CONNECTION=sqlite\n"                                    >> /var/www/html/.env
printf "DB_DATABASE=%s\n"       "${DB_DATABASE:-/var/www/html/database/database.sqlite}" >> /var/www/html/.env
printf "SESSION_DRIVER=file\n"                                     >> /var/www/html/.env
printf "CACHE_STORE=file\n"                                        >> /var/www/html/.env
printf "OPENPROJECT_URL=%s\n"   "${OPENPROJECT_URL:-}"            >> /var/www/html/.env
printf "OPENPROJECT_TOKEN=%s\n" "${OPENPROJECT_TOKEN:-}"          >> /var/www/html/.env
printf "GROQ_API_KEY=%s\n"      "${GROQ_API_KEY:-}"               >> /var/www/html/.env

# Si APP_KEY viene como variable de entorno, usarla; si no, dejar vacío para generarla
if [ -n "${APP_KEY}" ]; then
    printf "APP_KEY=%s\n" "${APP_KEY}" >> /var/www/html/.env
else
    printf "APP_KEY=\n" >> /var/www/html/.env
fi

# 2. Generar APP_KEY si no está definida
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "==> Generando APP_KEY..."
    php artisan key:generate --force
fi

# 3. Crear la base de datos SQLite si no existe
DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
if [ ! -f "$DB_PATH" ]; then
    echo "==> Creando base de datos SQLite..."
    touch "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
fi

# 4. Ejecutar migraciones
echo "==> Ejecutando migraciones..."
php artisan migrate --force --no-interaction

# 5. Optimizar
echo "==> Optimizando..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Permisos de storage
chown -R www-data:www-data storage bootstrap/cache

echo "==> Listo. Arrancando servicios..."
exec "$@"