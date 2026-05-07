FROM php:8.3-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y git unzip libpq-dev libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN if [ -f composer.lock ]; then composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader; else composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader; fi

EXPOSE 8002

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8002"]
