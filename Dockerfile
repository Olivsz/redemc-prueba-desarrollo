# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# base: PHP + Apache con lo que comparten todos los entornos.
# ---------------------------------------------------------------------------
FROM php:8.4-apache AS base

# Railway indica en PORT el puerto donde tiene que escuchar la app.
# Apache lee la variable al arrancar; 8080 es el valor por defecto en local.
ENV PORT=8080

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers \
    && sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf

COPY docker/apache/app.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/app.ini "$PHP_INI_DIR/conf.d/app.ini"

# ---------------------------------------------------------------------------
# dev: entorno local. El código se monta como volumen desde compose.yaml.
# ---------------------------------------------------------------------------
FROM base AS dev

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# vendor: dependencias de producción y autoloader optimizado.
# ---------------------------------------------------------------------------
FROM base AS vendor

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-autoloader
COPY src/ src/
RUN composer dump-autoload --no-dev --classmap-authoritative

# ---------------------------------------------------------------------------
# prod: imagen que publica Railway (es la última etapa, la que se construye por defecto).
# ---------------------------------------------------------------------------
FROM base AS prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=vendor /app/vendor/ vendor/
COPY src/ src/
COPY public/ public/

COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/app-entrypoint
CMD ["app-entrypoint"]
