# FinancePro API (Laravel 13) para Render.com: Nginx + PHP-FPM en un solo contenedor.
# Laravel 13 exige PHP >= 8.3; se usa 8.4, la misma versión del entorno local.
FROM php:8.4-fpm-bookworm

# Nginx, extensiones de PostgreSQL (Supabase) y utilidades para Composer.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install pdo_pgsql pgsql zip bcmath opcache \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# archivo de despliegue
# Primero solo las dependencias: esta capa se reutiliza mientras composer.lock no cambie.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

COPY . .

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# PHP en producción + OPcache, y FPM sin borrar las variables de entorno de Render.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY .render/php.ini "$PHP_INI_DIR/conf.d/zz-financepro.ini"
RUN printf '[www]\nclear_env = no\ncatch_workers_output = yes\ndecorate_workers_output = no\n' \
        > /usr/local/etc/php-fpm.d/zz-financepro.conf

COPY .render/nginx.conf /etc/nginx/financepro.conf.template
COPY .render/start.sh /usr/local/bin/start-financepro

# Si el archivo llegó con finales de línea de Windows, el shebang fallaría.
RUN sed -i 's/\r$//' /usr/local/bin/start-financepro \
    && chmod +x /usr/local/bin/start-financepro

# Render indica el puerto en $PORT; sin él se usa el 80.
EXPOSE 80

CMD ["start-financepro"]
