#!/bin/sh
set -e

# Crear las carpetas de writable/ si faltan (por ejemplo, al montar el código como volumen)
for dir in cache logs session uploads debugbar; do
    mkdir -p "/var/www/html/writable/$dir"
done
chown -R www-data:www-data /var/www/html/writable 2>/dev/null || true
chmod -R 775 /var/www/html/writable 2>/dev/null || true

# Instalar dependencias si el código montado no trae vendor/
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "vendor/ no encontrado: ejecutando composer install..."
    composer install --no-interaction --prefer-dist --working-dir=/var/www/html
fi

exec "$@"
