# Imagen de los nodos de Sudcali (Práctica 5).
# Compila también el CSS y el JavaScript: en el equipo solo hacen falta Git y Docker.

# 1. Dependencias de PHP sin las de desarrollo.
FROM composer:2 AS dependencias
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# 2. Las mismas, más las de desarrollo: solo para crear los datos de demostración (usa Faker).
FROM dependencias AS dependencias-preparar
RUN composer install --no-scripts --prefer-dist --no-interaction --ignore-platform-reqs

# 3. PHP con lo que pide el proyecto. composer.lock exige 8.4.1 o superior; aquí, la misma 8.5 del equipo.
FROM php:8.5-cli AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql zip
# No anunciar la versión de PHP.
RUN echo "expose_php=Off" > /usr/local/etc/php/conf.d/sudcali.ini
WORKDIR /app

# 4. CSS y JavaScript. Necesita PHP además de Node: el complemento Wayfinder ejecuta artisan al compilar.
FROM base AS frontend
COPY --from=node:24-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY --from=dependencias /app /app
RUN npm run build

# 5. Crea las tablas y los datos de demostración, y termina.
FROM base AS preparar
COPY --from=dependencias-preparar --chown=www-data:www-data /app /app
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/private bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
CMD ["php", "artisan", "migrate:fresh", "--seed", "--force"]

# 6. El nodo que atiende peticiones.
FROM base AS nodo
COPY --from=dependencias --chown=www-data:www-data /app /app
COPY --from=frontend --chown=www-data:www-data /app/public/build /app/public/build
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/private bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
# Sin privilegios de administrador dentro del contenedor.
USER www-data
EXPOSE 8000
# --no-reload permite usar varios procesos (PHP_CLI_SERVER_WORKERS).
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
