FROM dunglas/frankenphp:php8.3

RUN command -v frankenphp && ls -l "$(command -v frankenphp)"

WORKDIR /app

RUN install-php-extensions \
    pdo_pgsql \
    mbstring \
    bcmath \
    exif \
    pcntl \
    intl \
    zip \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache

ENV SERVER_NAME=:80

CMD ["sh", "-c", "frankenphp php-server -r public/ --listen 0.0.0.0:${PORT:-10000}"]