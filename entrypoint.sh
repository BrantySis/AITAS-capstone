#!/bin/bash
set -e

echo "🚀 Starting Laravel Cloud Run Entrypoint..."

# Ensure correct permissions for Laravel
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear old caches (to avoid stale configs)
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Check if .env file exists
if [ ! -f .env ]; then
  echo "⚠️  .env file missing! Please include it or mount environment variables."
fi

# Wait for database connection if configured
if [ -n "${DB_HOST}" ]; then
  echo "⏳ Waiting for database ($DB_HOST) to be ready..."
  until php artisan migrate:status >/dev/null 2>&1; do
    echo "   Database not ready yet. Retrying in 5s..."
    sleep 5
  done
fi

# Run migrations (idempotent — won’t re-run same batch)
echo "🗄️  Running migrations..."
php artisan migrate --force

# Optional seeding
if [ "${SEED_DB}" = "true" ]; then
  echo "🌱 Seeding database..."
  php artisan db:seed --force
fi

# Regenerate cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run Laravel queue if needed (optional background)
# php artisan queue:work --daemon &

echo "✅ Starting Apache on port 8080..."
exec apache2-foreground
