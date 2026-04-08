FROM php:8.1-fpm

# Evita prompts
ENV DEBIAN_FRONTEND=noninteractive

# Instala dependências de sistema
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

# Configura GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instala extensões PHP
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

# Instala extensão Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia do projeto raiz (contexto do Railway)
COPY backend/composer.json backend/composer.lock ./
COPY backend/artisan ./
COPY backend/app ./app
COPY backend/bootstrap ./bootstrap
COPY backend/config ./config
COPY backend/database ./database
COPY backend/public ./public
COPY backend/routes ./routes

# Cria pastas necessárias
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap
RUN chmod -R 775 /var/www/storage /var/www/bootstrap

# Instala dependências Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Limpa cache
RUN php artisan config:clear || true

# Alterna para usuário não-root
USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]