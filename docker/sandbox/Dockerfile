FROM phpswoole/swoole:6.0-php8.2-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache bash libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath pcntl

# ── 1. Copia apenas os fontes da lib (sem vendor) ────────────────────────
RUN mkdir -p /matrix-lib
COPY composer.json /matrix-lib/composer.json
COPY src/ /matrix-lib/src/

# ── 2. Instala o sandbox ─────────────────────────────────────────────────
WORKDIR /app
COPY sandbox/ .

# Aponta path repo para /matrix-lib e instala
RUN composer config repositories.matrix \
    '{"type":"path","url":"/matrix-lib","options":{"symlink":false}}' \
    && composer require ananiaslitz/matrix:@dev --no-interaction \
    && composer dump-autoload --optimize \
    && rm -rf runtime/container

EXPOSE 9501

CMD ["php", "bin/hyperf.php", "start"]
