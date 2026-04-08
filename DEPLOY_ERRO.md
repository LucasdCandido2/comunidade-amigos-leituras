 
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
887ms

internal
load metadata for docker.io/library/php:8.1-fpm
850ms

internal
load .dockerignore
0ms

internal
load build context
0ms

stage-0
FROM docker.io/library/php:8.1-fpm@sha256:a3118db1911fdd3b3ac66605122ddc859286688ced86fc860fec6d19cc2d6c55
9ms

FROM docker.io/library/composer:2@sha256:743aebe48ca67097c36819040633ea77e44a561eca135e4fc84c002e63a1ba07
10ms

stage-0
RUN pecl install redis && docker-php-ext-enable redis cached
0ms

stage-0
RUN apt-get update && apt-get install -y --no-install-recommends     git     curl     ca-certificates     build-essential     pkg-config     libpng-dev     libjpeg-dev     libfreetype6-dev     libonig-dev     libxml2-dev     zip     unzip     zlib1g-dev     libzip-dev     libpq-dev     libicu-dev     && rm -rf /var/lib/apt/lists/* cached
0ms

stage-0
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer cached
0ms

stage-0
RUN docker-php-ext-install -j$(nproc)     pdo_pgsql     pgsql     mbstring     exif     pcntl     bcmath     gd     zip     intl cached
0ms

stage-0
RUN docker-php-ext-configure gd --with-freetype --with-jpeg cached
0ms

stage-0
WORKDIR /var/www cached
0ms

stage-0
COPY composer.json composer.lock ./ cached
0ms

stage-0
COPY app ./app cached
0ms

stage-0
COPY bootstrap ./bootstrap cached
0ms

stage-0
COPY database ./database cached
0ms

stage-0
COPY public ./public
0ms

stage-0
COPY config ./config cached
0ms

stage-0
COPY tests ./tests cached
0ms

stage-0
COPY storage ./storage cached
0ms

stage-0
COPY .env.example ./
0ms

stage-0
COPY routes ./routes cached
0ms
Dockerfile:59
-------------------
57 |     COPY storage ./storage
58 |     COPY tests ./tests
59 | >>> COPY .env.example ./
60 |
61 |     # Cria pastas necessárias
-------------------
ERROR: failed to build: failed to solve: failed to compute cache key: failed to calculate checksum of ref fingdxlardqd1w1pluqng8hk1::vajpigtwreqfxqq01865gwbzk: "/.env.example": not found



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