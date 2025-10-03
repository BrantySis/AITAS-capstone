#!/bin/bash
set -e

echo "Waiting for database to be ready..."
until php artisan migrate:status >/dev/null 2>&1; do
  echo "Database not ready yet, retrying in 5s..."
  sleep 5
done

echo "Running migrations..."
php artisan migrate --force

# Run seeds only if needed (example: run only in production, or first deploy)
if [ "${SEED_DB}" = "true" ]; then
  echo "Seeding database..."
  php artisan db:seed --force
fi

echo "Starting Apache..."
exec apache2-foreground
