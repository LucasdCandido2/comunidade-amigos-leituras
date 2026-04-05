FROM php:8.1-fpm

# Evita prompts
ENV DEBIAN_FRONTEND=noninteractive

# Instala dependências de sistema necessárias para compilar extensões PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    ca-certificates \
    build-essential \
    pkg-config \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Configura GD (necessário informar as bibliotecas jpeg/freetype)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instala as extensões PHP necessárias para Laravel/Postgres
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Cria usuário não-root (UID 1000) e grupo
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Cria pastas necessárias para permissões
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache

# Ajusta permissões das pastas críticas
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap
RUN chmod -R 775 /var/www/storage /var/www/bootstrap

# Copia configuração PHP de desenvolvimento (desabilita OPcache para evitar SIGSEGV)
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-dev.ini

# Alterna para o usuário não-root
USER www-data