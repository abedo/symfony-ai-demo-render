FROM php:8.4-apache-bookworm

# Instalacja zależności systemowych i rozszerzeń PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    zip \
    gd \
    intl \
    pdo \
    pdo_pgsql \
    opcache \
    && rm -rf /var/lib/apt/lists/*

# Włączenie modułu mod_rewrite w Apache (kluczowe dla routingu Symfony)
RUN a2enmod rewrite

# Konfiguracja Apache DocumentRoot na folder /public dla Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Konfiguracja Apache do nasłuchiwania na dynamicznym porcie ($PORT) przydzielonym przez Render.com
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT}/' /etc/apache2/sites-available/000-default.conf

# Środowisko produkcyjne
ENV APP_ENV=prod
ENV APP_DEBUG=0

WORKDIR /var/www/html
COPY . .

# Instalacja Composera i zależności
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --no-interaction --optimize-autoloader --classmap-authoritative

# Czyszczenie cache i kompilacja assetów
RUN php bin/console cache:clear
RUN php bin/console asset-map:compile

# Nadanie uprawnień do uruchomienia skryptu startowego
RUN chmod +x docker-entrypoint.sh

EXPOSE 10000

CMD ["./docker-entrypoint.sh"]
