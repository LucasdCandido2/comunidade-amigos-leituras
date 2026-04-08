

stage-0
RUN docker-php-ext-install -j$(nproc)     pdo_pgsql     pgsql     mbstring     exif     pcntl     bcmath     gd     zip     intl
31s
rm -f ext/opcache/minilua

stage-0
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
76ms

stage-0
WORKDIR /var/www
18ms

stage-0
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache
77ms

stage-0
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap
146ms

stage-0
RUN chmod -R 775 /var/www/storage /var/www/bootstrap
81ms

stage-0
COPY . .
213ms

stage-0
RUN composer install --no-dev --optimize-autoloader --no-interaction
167ms
Composer could not find a composer.json file in /var/www
To initialize a project, please create a composer.json file. See https://getcomposer.org/basic-usage
Dockerfile:57
-------------------
55 |
56 |     # Instala dependências Laravel
57 | >>> RUN composer install --no-dev --optimize-autoloader --no-interaction
58 |
59 |     # Gera chave da aplicação se não existir
-------------------
ERROR: failed to build: failed to solve: process "/bin/sh -c composer install --no-dev --optimize-autoloader --no-interaction" did not complete successfully: exit code: 1