FROM php:8.3-fpm-alpine3.22

RUN for attempt in 1 2 3; do \
        apk add --no-cache \
    postgresql-dev \
    icu-dev \
    nginx \
    supervisor \
    nodejs \
    npm \
    curl \
    postgresql-client \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    linux-headers \
        && break; \
        if [ "$attempt" = 3 ]; then exit 1; fi; \
        sleep 5; \
    done

RUN docker-php-ext-install pdo_pgsql bcmath intl mbstring xml zip pcntl

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/www/html

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY entrypoint.sh /usr/local/bin/entrypoint

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-dev \
    && npm install \
    && npm run build \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && rm -f bootstrap/cache/*.php

RUN chmod +x /usr/local/bin/entrypoint \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=5 CMD curl --fail --silent http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint"]

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
