#!/bin/sh
# Arranque del contenedor en Render: prepara Laravel y levanta PHP-FPM + Nginx.
set -e

cd /var/www/html

PORT="${PORT:-80}"
sed "s/__PORT__/${PORT}/g" /etc/nginx/financepro.conf.template > /etc/nginx/conf.d/financepro.conf

# Base de datos al día antes de recibir tráfico (si falla, el despliegue se detiene).
php artisan migrate --force --no-interaction

# Cachés con las variables de entorno de este despliegue.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Enlace público para las fotos de perfil (storage/app/public -> public/storage).
php artisan storage:link --force >/dev/null 2>&1 || true

chown -R www-data:www-data storage bootstrap/cache

php-fpm --daemonize

echo "FinancePro API escuchando en el puerto ${PORT}"
exec nginx -g 'daemon off;'
