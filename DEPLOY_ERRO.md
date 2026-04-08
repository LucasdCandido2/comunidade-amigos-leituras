[Region: asia-southeast1]
=========================
Using Detected Dockerfile
=========================

context: 0w20-u4R4

internal
load build definition from Dockerfile
0ms

internal
load metadata for docker.io/library/composer:2
821ms

internal
load metadata for docker.io/library/php:8.1-fpm
998ms

auth
library/composer:pull token for registry-1.docker.io
0ms

auth
library/php:pull token for registry-1.docker.io
0ms

internal
load .dockerignore
1ms

stage-0
FROM docker.io/library/php:8.1-fpm@sha256:a3118db1911fdd3b3ac66605122ddc859286688ced86fc860fec6d19cc2d6c55
12ms

internal
load build context
0ms

FROM docker.io/library/composer:2@sha256:743aebe48ca67097c36819040633ea77e44a561eca135e4fc84c002e63a1ba07
11ms

stage-0
WORKDIR /var/www cached
0ms

stage-0
RUN pecl install redis && docker-php-ext-enable redis cached
0ms

stage-0
RUN docker-php-ext-install -j$(nproc)     pdo_pgsql     pgsql     mbstring     exif     pcntl     bcmath     gd     zip     intl cached
0ms

stage-0
RUN docker-php-ext-configure gd --with-freetype --with-jpeg cached
0ms

stage-0
RUN apt-get update && apt-get install -y --no-install-recommends     git     curl     ca-certificates     build-essential     pkg-config     libpng-dev     libjpeg-dev     libfreetype6-dev     libonig-dev     libxml2-dev     zip     unzip     zlib1g-dev     libzip-dev     libpq-dev     libicu-dev     && rm -rf /var/lib/apt/lists/* cached
0ms

stage-0
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer cached
0ms

stage-0
COPY backend/composer.json backend/composer.lock ./
29ms

stage-0
COPY backend/app ./app
17ms

stage-0
COPY backend/bootstrap ./bootstrap
13ms

stage-0
COPY backend/config ./config
14ms

stage-0
COPY backend/database ./database
15ms

stage-0
COPY backend/public ./public
13ms

stage-0
COPY backend/routes ./routes
50ms

stage-0
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache
144ms

stage-0
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap
120ms

stage-0
RUN chmod -R 775 /var/www/storage /var/www/bootstrap
158ms

stage-0
RUN composer install --no-dev --optimize-autoloader --no-interaction
7s
Installing dependencies from lock file
Verifying lock file contents can be installed on current platform.
Package operations: 86 installs, 0 updates, 0 removals
  - Downloading php-http/discovery (1.20.0)
  - Downloading voku/portable-ascii (2.0.3)
  - Downloading symfony/polyfill-php80 (v1.33.0)
  - Downloading symfony/polyfill-mbstring (v1.33.0)
  - Downloading symfony/polyfill-ctype (v1.33.0)
  - Downloading phpoption/phpoption (1.9.5)
  - Downloading graham-campbell/result-type (v1.1.4)
  - Downloading vlucas/phpdotenv (v5.6.3)
  - Downloading symfony/css-selector (v6.4.34)
  - Downloading tijsverkoyen/css-to-inline-styles (v2.4.0)
  - Downloading symfony/deprecation-contracts (v3.6.0)
  - Downloading symfony/var-dumper (v6.4.32)
  - Downloading symfony/polyfill-uuid (v1.33.0)
  - Downloading symfony/uid (v6.4.32)
  - Downloading symfony/routing (v6.4.34)
  - Downloading symfony/process (v6.4.33)
  - Downloading symfony/polyfill-intl-normalizer (v1.33.0)
  - Downloading symfony/polyfill-intl-idn (v1.33.0)
  - Downloading symfony/mime (v6.4.35)
  - Downloading psr/container (2.0.2)
  - Downloading symfony/service-contracts (v3.6.1)
  - Downloading psr/event-dispatcher (1.0.0)
  - Downloading symfony/event-dispatcher-contracts (v3.6.0)
  - Downloading symfony/event-dispatcher (v6.4.32)
  - Downloading psr/log (3.0.2)
  - Downloading doctrine/lexer (3.0.1)
  - Downloading egulias/email-validator (4.0.4)
  - Downloading symfony/mailer (v6.4.34)
  - Downloading symfony/polyfill-php83 (v1.33.0)
  - Downloading symfony/http-foundation (v6.4.35)
  - Downloading symfony/error-handler (v6.4.32)
  - Downloading symfony/http-kernel (v6.4.35)
  - Downloading symfony/finder (v6.4.34)
  - Downloading symfony/polyfill-intl-grapheme (v1.33.0)
  - Downloading symfony/string (v6.4.34)
  - Downloading symfony/console (v6.4.35)
  - Downloading ramsey/collection (2.1.1)
  - Downloading brick/math (0.12.3)
  - Downloading ramsey/uuid (4.9.2)
  - Downloading psr/simple-cache (3.0.0)
  - Downloading nunomaduro/termwind (v1.17.0)
  - Downloading symfony/translation-contracts (v3.6.1)
  - Downloading symfony/translation (v6.4.34)
  - Downloading psr/clock (1.0.0)
  - Downloading carbonphp/carbon-doctrine-types (2.1.0)
  - Downloading nesbot/carbon (2.73.0)
  - Downloading monolog/monolog (3.10.0)
  - Downloading league/mime-type-detection (1.16.0)
  - Downloading league/flysystem (3.33.0)
  - Downloading league/flysystem-local (3.31.0)
  - Downloading nette/utils (v4.0.10)
  - Downloading nette/schema (v1.3.5)
  - Downloading dflydev/dot-access-data (v3.0.3)
  - Downloading league/config (v1.2.0)
  - Downloading league/commonmark (2.8.2)
  - Downloading laravel/serializable-closure (v1.3.7)
  - Downloading laravel/framework (10.50.2)
  - Downloading laravel/prompts (v0.1.25)
  - Downloading guzzlehttp/uri-template (v1.0.5)
  - Downloading fruitcake/php-cors (v1.4.0)
  - Downloading dragonmantank/cron-expression (v3.5.0)
  - Downloading doctrine/inflector (2.1.0)
  - Downloading masterminds/html5 (2.10.0)
  - Downloading thecodingmachine/safe (v3.4.0)
  - Downloading sabberworm/php-css-parser (v9.3.0)
  - Downloading dompdf/php-svg-lib (1.0.2)
  - Downloading dompdf/php-font-lib (1.0.2)
  - Downloading dompdf/dompdf (v3.1.5)
  - Downloading barryvdh/laravel-dompdf (v3.1.2)
  - Downloading psr/http-message (2.0)
  - Downloading psr/http-client (1.0.3)
  - Downloading ralouphie/getallheaders (3.0.3)
  - Downloading psr/http-factory (1.1.0)
  - Downloading guzzlehttp/psr7 (2.9.0)
  - Downloading guzzlehttp/promises (2.3.0)
  - Downloading guzzlehttp/guzzle (7.10.0)
  - Downloading intervention/gif (4.2.4)
  - Downloading intervention/image (3.11.7)
  - Downloading laravel/sanctum (v3.3.3)
  - Downloading laravel/scout (v11.1.0)
  - Downloading nikic/php-parser (v5.7.0)
  - Downloading psy/psysh (v0.12.22)
  - Downloading laravel/tinker (v2.11.1)
  - Downloading symfony/polyfill-php81 (v1.33.0)
  - Downloading meilisearch/meilisearch-php (v1.16.1)
  - Downloading predis/predis (v3.4.2)
  0/86 [>---------------------------]   0%
  8/86 [==>-------------------------]   9%
 11/86 [===>------------------------]  12%
 18/86 [=====>----------------------]  20%
 26/86 [========>-------------------]  30%
 36/86 [===========>----------------]  41%
 43/86 [==============>-------------]  50%
 53/86 [=================>----------]  61%
 62/86 [====================>-------]  72%
 69/86 [======================>-----]  80%
 79/86 [=========================>--]  91%
 86/86 [============================] 100%
  - Installing php-http/discovery (1.20.0): Extracting archive
  - Installing voku/portable-ascii (2.0.3): Extracting archive
  - Installing symfony/polyfill-php80 (v1.33.0): Extracting archive
  - Installing symfony/polyfill-mbstring (v1.33.0): Extracting archive
  - Installing symfony/polyfill-ctype (v1.33.0): Extracting archive
  - Installing phpoption/phpoption (1.9.5): Extracting archive
  - Installing graham-campbell/result-type (v1.1.4): Extracting archive
  - Installing vlucas/phpdotenv (v5.6.3): Extracting archive
  - Installing symfony/css-selector (v6.4.34): Extracting archive
  - Installing tijsverkoyen/css-to-inline-styles (v2.4.0): Extracting archive
  - Installing symfony/deprecation-contracts (v3.6.0): Extracting archive
  - Installing symfony/var-dumper (v6.4.32): Extracting archive
  - Installing symfony/polyfill-uuid (v1.33.0): Extracting archive
  - Installing symfony/uid (v6.4.32): Extracting archive
  - Installing symfony/routing (v6.4.34): Extracting archive
  - Installing symfony/process (v6.4.33): Extracting archive
  - Installing symfony/polyfill-intl-normalizer (v1.33.0): Extracting archive
  - Installing symfony/polyfill-intl-idn (v1.33.0): Extracting archive
  - Installing symfony/mime (v6.4.35): Extracting archive
  - Installing psr/container (2.0.2): Extracting archive
  - Installing symfony/service-contracts (v3.6.1): Extracting archive
  - Installing psr/event-dispatcher (1.0.0): Extracting archive
  - Installing symfony/event-dispatcher-contracts (v3.6.0): Extracting archive
  - Installing symfony/event-dispatcher (v6.4.32): Extracting archive
  - Installing psr/log (3.0.2): Extracting archive
  - Installing doctrine/lexer (3.0.1): Extracting archive
  - Installing egulias/email-validator (4.0.4): Extracting archive
  - Installing symfony/mailer (v6.4.34): Extracting archive
  - Installing symfony/polyfill-php83 (v1.33.0): Extracting archive
  - Installing symfony/http-foundation (v6.4.35): Extracting archive
  - Installing symfony/error-handler (v6.4.32): Extracting archive
  - Installing symfony/http-kernel (v6.4.35): Extracting archive
  - Installing symfony/finder (v6.4.34): Extracting archive
  - Installing symfony/polyfill-intl-grapheme (v1.33.0): Extracting archive
  - Installing symfony/string (v6.4.34): Extracting archive
  - Installing symfony/console (v6.4.35): Extracting archive
  - Installing ramsey/collection (2.1.1): Extracting archive
  - Installing brick/math (0.12.3): Extracting archive
  - Installing ramsey/uuid (4.9.2): Extracting archive
  - Installing psr/simple-cache (3.0.0): Extracting archive
  - Installing nunomaduro/termwind (v1.17.0): Extracting archive
  - Installing symfony/translation-contracts (v3.6.1): Extracting archive
  - Installing symfony/translation (v6.4.34): Extracting archive
  - Installing psr/clock (1.0.0): Extracting archive
  - Installing carbonphp/carbon-doctrine-types (2.1.0): Extracting archive
  - Installing nesbot/carbon (2.73.0): Extracting archive
  - Installing monolog/monolog (3.10.0): Extracting archive
  - Installing league/mime-type-detection (1.16.0): Extracting archive
  - Installing league/flysystem (3.33.0): Extracting archive
  - Installing league/flysystem-local (3.31.0): Extracting archive
  - Installing nette/utils (v4.0.10): Extracting archive
  - Installing nette/schema (v1.3.5): Extracting archive
  - Installing dflydev/dot-access-data (v3.0.3): Extracting archive
  - Installing league/config (v1.2.0): Extracting archive
  - Installing league/commonmark (2.8.2): Extracting archive
  - Installing laravel/serializable-closure (v1.3.7): Extracting archive
  - Installing laravel/framework (10.50.2): Extracting archive
  - Installing laravel/prompts (v0.1.25): Extracting archive
  - Installing guzzlehttp/uri-template (v1.0.5): Extracting archive
  - Installing fruitcake/php-cors (v1.4.0): Extracting archive
  - Installing dragonmantank/cron-expression (v3.5.0): Extracting archive
  - Installing doctrine/inflector (2.1.0): Extracting archive
  - Installing masterminds/html5 (2.10.0): Extracting archive
  - Installing thecodingmachine/safe (v3.4.0): Extracting archive
  - Installing sabberworm/php-css-parser (v9.3.0): Extracting archive
  - Installing dompdf/php-svg-lib (1.0.2): Extracting archive
  - Installing dompdf/php-font-lib (1.0.2): Extracting archive
  - Installing dompdf/dompdf (v3.1.5): Extracting archive
  - Installing barryvdh/laravel-dompdf (v3.1.2): Extracting archive
  - Installing psr/http-message (2.0): Extracting archive
  - Installing psr/http-client (1.0.3): Extracting archive
  - Installing ralouphie/getallheaders (3.0.3): Extracting archive
  - Installing psr/http-factory (1.1.0): Extracting archive
  - Installing guzzlehttp/psr7 (2.9.0): Extracting archive
  - Installing guzzlehttp/promises (2.3.0): Extracting archive
  - Installing guzzlehttp/guzzle (7.10.0): Extracting archive
  - Installing intervention/gif (4.2.4): Extracting archive
  - Installing intervention/image (3.11.7): Extracting archive
  - Installing laravel/sanctum (v3.3.3): Extracting archive
  - Installing laravel/scout (v11.1.0): Extracting archive
  - Installing nikic/php-parser (v5.7.0): Extracting archive
  - Installing psy/psysh (v0.12.22): Extracting archive
  - Installing laravel/tinker (v2.11.1): Extracting archive
  - Installing symfony/polyfill-php81 (v1.33.0): Extracting archive
  - Installing meilisearch/meilisearch-php (v1.16.1): Extracting archive
  - Installing predis/predis (v3.4.2): Extracting archive
  0/85 [>---------------------------]   0%
 30/85 [=========>------------------]  35%
 54/85 [=================>----------]  63%
 75/85 [========================>---]  88%
 85/85 [============================] 100%
Generating optimized autoload files
> Illuminate\Foundation\ComposerScripts::postAutoloadDump
> @php artisan package:discover --ansi
Could not open input file: artisan
Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 1
Dockerfile:66
-------------------
64 |
65 |     # Instala dependências Laravel
66 | >>> RUN composer install --no-dev --optimize-autoloader --no-interaction
67 |
68 |     # Limpa cache
-------------------
ERROR: failed to build: failed to solve: process "/bin/sh -c composer install --no-dev --optimize-autoloader --no-interaction" did not complete successfully: exit code: 1



esse é o json de configuração do railway

{
  "$schema": "https://railway.com/railway.schema.json",
  "build": {
    "builder": "DOCKERFILE",
    "dockerfilePath": "/backend/Dockerfile"
  },
  "deploy": {
    "runtime": "V2",
    "numReplicas": 1,
    "startCommand": "php artisan serve --host=0.0.0.0 --port=8000",
    "preDeployCommand": [
      "composer install --no-dev --optimize-autoloader"
    ],
    "sleepApplication": false,
    "useLegacyStacker": false,
    "ipv6EgressEnabled": false,
    "multiRegionConfig": {
      "asia-southeast1-eqsg3a": {
        "numReplicas": 1
      }
    },
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}