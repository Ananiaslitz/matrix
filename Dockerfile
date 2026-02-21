FROM phpswoole/swoole:6.0-php8.2-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache bash libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

WORKDIR /app

COPY composer.json ./

RUN composer update --no-interaction --no-scripts --prefer-dist

COPY . .

CMD ["bash"]
