FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        postgresql-client \
        postgresql-dev \
        libpng-dev \
        libzip-dev \
        libwebp-dev \
        oniguruma-dev \
        icu-dev \
        freetype-dev \
        libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        mbstring \
        bcmath \
        zip \
        gd \
        intl \
        opcache \
        pcntl \
    && apk del --no-cache postgresql-dev libpng-dev libzip-dev libwebp-dev freetype-dev libjpeg-turbo-dev icu-dev oniguruma-dev

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-www.conf

WORKDIR /var/www/html

RUN addgroup -g 1002 developer 2>/dev/null || true \
    && adduser -D -u 1002 -G developer developer 2>/dev/null || true

CMD ["php-fpm"]
