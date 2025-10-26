#!/bin/sh

# Fail fast
set -e

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
  echo "APP_KEY is not set - generating application key"
  php artisan key:generate --force || true
else
  echo "APP_KEY provided via environment"
fi

# Attendre que la base de données soit prête (avec timeout)
echo "Waiting for database to be ready..."
timeout=60
counter=0
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
  echo "Database is unavailable - sleeping ($counter/$timeout)"
  sleep 1
  counter=$((counter + 1))
  if [ $counter -ge $timeout ]; then
    echo "Database connection timeout - starting application anyway"
    break
  fi
done

if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; then
  echo "Database is up - executing migrations"
  php artisan migrate --force || true
else
  echo "Database is not available - skipping migrations"
fi

# Nettoyer et optimiser l'application
echo "Clearing and optimizing Laravel caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Générer la documentation Swagger une fois en production si demandé
if [ "${L5_SWAGGER_GENERATE_ALWAYS}" = "false" ] || [ -n "$RENDER" ]; then
  echo "Generating Swagger docs..."
  mkdir -p storage/api-docs || true
  php artisan l5-swagger:generate || true
fi

echo "Starting Laravel application..."
exec "$@"
