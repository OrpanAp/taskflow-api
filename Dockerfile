# Stage 1: Build the frontend assets with Vite
FROM node:22-bookworm-slim AS frontend

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build


# Stage 2: Build the Laravel PHP application
FROM php:8.4-cli-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip \
    opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Keep Laravel's dotenv reader quiet without baking secrets into the image.
RUN touch .env

# Include the compiled Vite assets in the final PHP image
COPY --from=frontend /var/www/html/public/build ./public/build

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
