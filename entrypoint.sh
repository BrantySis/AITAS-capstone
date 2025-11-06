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

# ✅ Run the scheduler in background
echo "Starting Laravel scheduler..."
(while true; do php artisan schedule:run >> /dev/null 2>&1; sleep 60; done) &

echo "Starting Apache..."
exec apache2-foreground




# # entrypoint.sh
# set -e

# echo "Waiting for database to be ready..."
# until php artisan migrate:status >/dev/null 2>&1; do
#   echo "Database not ready yet, retrying in 5s..."
#   sleep 5
# done

# # Run migrations only if not yet migrated
# if php artisan migrate:status | grep -q "No migrations found"; then
#   echo "Running migrations..."
#   php artisan migrate --force
# else
#   echo "Migrations already up to date."
# fi
