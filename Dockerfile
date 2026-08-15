# --- Stage 1: Build Frontend Assets ---
FROM node:24 AS frontend
WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources/ ./resources/
RUN npm ci && npm run build

# --- Stage 2: Build PHP Dependencies ---
FROM php:8.5-cli AS vendor

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    sqlite3 \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_sqlite mbstring zip bcmath xml ctype fileinfo

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-scripts --ignore-platform-reqs

# --- Stage 3: Final Production Environment ---
FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    sqlite3 \
    libsqlite3-dev \
    libpng-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_sqlite gd zip

WORKDIR /var/www

# Copy PHP application and built vendor dependencies
COPY --from=vendor /app /var/www

# Copy compiled frontend assets from Stage 1 into public/build
COPY --from=frontend /app/public/build /var/www/public/build

# Ensure SQLite file exists and permissions are set
RUN touch /var/www/database/database.sqlite
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database

# Configure Nginx
RUN echo 'server { \
    listen 80; \
    index index.php index.html; \
    root /var/www/public; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/sites-available/default

EXPOSE 80

CMD php artisan migrate --force && php-fpm -D && nginx -g 'daemon off;'