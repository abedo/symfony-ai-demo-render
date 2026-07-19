FROM dunglas/frankenphp:1-php8.4-alpine

# Instalacja zależności systemowych i rozszerzeń PHP
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    zip \
    gd \
    intl \
    pdo \
    pdo_pgsql \
    opcache

# Ustawienie środowiska produkcyjnego
ENV APP_ENV=prod
ENV APP_DEBUG=0

WORKDIR /app
COPY . .

# Instalacja zależności Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative

# Czyszczenie i rozgrzanie pamięci podręcznej oraz kompilacja assetów (Asset Mapper)
RUN php bin/console cache:clear
RUN php bin/console asset-map:compile

EXPOSE 80
EXPOSE 443

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
