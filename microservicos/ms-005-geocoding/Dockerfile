FROM php:8.3-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./

RUN if [ -f composer.lock ]; then composer install --no-interaction --prefer-dist; else composer install --no-interaction --prefer-dist; fi

COPY . .

EXPOSE 8005

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8005"]
