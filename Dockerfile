# Étape 1: Build des dépendances PHP
FROM php:8.3-alpine AS composer-build

WORKDIR /app

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les outils nécessaires pour Composer
RUN apk add --no-cache unzip git

# Installer les dépendances PHP sans scripts post-install
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Étape 2: Image finale pour l'application
FROM php:8.3-fpm-alpine

# Installer les extensions PHP nécessaires
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les dépendances installées depuis l'étape de build
COPY --from=composer-build /app/vendor ./vendor

# Copier le reste du code de l'application
COPY . .

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Créer un fichier .env minimal pour le build
RUN echo "APP_NAME=Laravel" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_KEY=" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=http://localhost" >> .env && \
    echo "" >> .env && \
    echo "LOG_CHANNEL=stack" >> .env && \
    echo "LOG_LEVEL=error" >> .env && \
    echo "" >> .env && \
    echo "DB_CONNECTION=pgsql" >> .env && \
    echo "DB_HOST=\${DB_HOST}" >> .env && \
    echo "DB_PORT=\${DB_PORT}" >> .env && \
    echo "DB_DATABASE=\${DB_DATABASE}" >> .env && \
    echo "DB_USERNAME=\${DB_USERNAME}" >> .env && \
    echo "DB_PASSWORD=\${DB_PASSWORD}" >> .env && \
    echo "" >> .env && \
    echo "CACHE_DRIVER=file" >> .env && \
    echo "SESSION_DRIVER=file" >> .env && \
    echo "QUEUE_CONNECTION=sync" >> .env

# Changer les permissions du fichier .env pour l'utilisateur laravel
RUN chown laravel:laravel .env

# Générer la clé d'application et optimiser
USER laravel
RUN php artisan key:generate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache
USER root

# Copier le script d'entrée
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Passer à l'utilisateur non-root
USER laravel

# Exposer le port 10000
EXPOSE 10000

# Commande par défaut
CMD ["/usr/local/bin/start.sh", "php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]