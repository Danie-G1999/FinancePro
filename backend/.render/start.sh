#!/usr/bin/env bash
# Arranque del contenedor en Render: prepara Laravel y levanta PHP-FPM + Nginx.
# Los pasos de preparación no detienen el contenedor si fallan: se registran en los
# logs de Render para poder diagnosticarlos con el servicio en marcha.

cd /var/www/html || exit 1

log() {
    echo "[start] $*"
}

# --- 1. Variables obligatorias (solo se muestran los nombres, nunca los valores) ---
missing=()
for var in APP_KEY DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    if [ -z "${!var}" ]; then
        missing+=("$var")
    fi
done

if [ ${#missing[@]} -gt 0 ]; then
    log "ATENCIÓN: faltan variables de entorno en Render: ${missing[*]}"
    log "Sin DB_CONNECTION=pgsql Laravel usa SQLite y no encontrará la base."
fi

# --- 2. Nginx en el puerto que asigna Render ---
PORT="${PORT:-80}"
sed "s/__PORT__/${PORT}/g" /etc/nginx/financepro.conf.template > /etc/nginx/conf.d/financepro.conf

# --- 3. Limpiar cachés de un arranque anterior ---
php artisan config:clear || log "No se pudo limpiar la caché de configuración."
php artisan cache:clear || log "No se pudo limpiar la caché de la app (usa la base de datos: revisa la conexión)."

# --- 4. Migraciones, con reintentos (el pooler de Supabase puede tardar en responder) ---
migrated=false
for attempt in 1 2 3; do
    if php artisan migrate --force --no-interaction; then
        migrated=true
        break
    fi

    log "Migraciones fallaron (intento ${attempt}/3). Reintentando en 5 s..."
    sleep 5
done

if [ "$migrated" != true ]; then
    log "ERROR: no se pudieron ejecutar las migraciones. La app arranca, pero revisa DB_* y DB_SSLMODE."
fi

# --- 5. Cachés con las variables de este despliegue ---
php artisan config:cache || log "No se pudo cachear la configuración."
php artisan route:cache || log "No se pudieron cachear las rutas."
php artisan view:cache || log "No se pudieron cachear las vistas."

# Enlace público para las fotos de perfil (storage/app/public -> public/storage).
php artisan storage:link --force >/dev/null 2>&1 || true

chown -R www-data:www-data storage bootstrap/cache

# --- 6. Servicios en primer plano: si cualquiera se detiene, el contenedor termina
#        y Render lo reinicia (en vez de quedar "vivo" sin poder atender peticiones). ---
nginx -t || { log "ERROR: configuración de Nginx inválida."; exit 1; }

php-fpm -F &
fpm_pid=$!

nginx -g 'daemon off;' &
nginx_pid=$!

log "FinancePro API escuchando en el puerto ${PORT}"

trap 'kill -TERM "$fpm_pid" "$nginx_pid" 2>/dev/null' TERM INT

wait -n "$fpm_pid" "$nginx_pid"
status=$?

log "Un servicio se detuvo (código ${status}). Cerrando el contenedor."
kill -TERM "$fpm_pid" "$nginx_pid" 2>/dev/null
exit "$status"
