#!/bin/bash
set -e

echo "==> Installing Composer dependencies..."
composer install --no-interaction --optimize-autoloader

echo "==> Waiting for database..."
wait-for-it "$DB_HOST:3306" --timeout=60 --strict -- echo "Database is up"

echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "==> Starting services..."

if [ "${ENABLE_CONSUMER}" = "true" ]; then
    echo "==> Starting messenger consumer in background..."
    nohup APP_DEBUG=0 php bin/console messenger:consume product_updates --time-limit=3600 >> /var/log/consumer.log 2>&1 &
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
