# =============================================================================
# UGR Portfolio — Dockerfile de producción
# PHP 8.2 + Nginx + Supervisor
# Nota: el proyecto usa Tailwind CSS via CDN y Alpine.js via CDN,
# por lo que no se requiere compilación de assets con Node/Vite.
# =============================================================================

FROM php:8.2-fpm-alpine AS production

# Instalar extensiones PHP necesarias para Laravel + utilidades del sistema
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        libpng-dev \
        libxml2-dev \
        sqlite-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        mbstring \
        xml \
        gd \
        bcmath

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar código fuente completo
COPY . .

# Instalar dependencias PHP (solo producción, sin paquetes de desarrollo)
RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --ignore-platform-reqs \
        --quiet

# Copiar configuraciones de Nginx y Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Crear directorios de storage y asignar permisos a www-data
RUN mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]