#!/bin/sh

# Attendre que la base de données soit prête (avec timeout)
echo "Waiting for database to be ready..."
timeout=60
counter=0
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  echo "Database is unavailable - sleeping ($counter/$timeout)"
  sleep 1
  counter=$((counter + 1))
  if [ $counter -ge $timeout ]; then
    echo "Database connection timeout - starting application anyway"
    break
  fi
done

if pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; then
  echo "Database is up - executing migrations"
  php artisan migrate --force
else
  echo "Database is not available - skipping migrations"
fi

# Optimiser l'application pour la production
echo "Optimizing Laravel application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel application..."
exec "$@"
