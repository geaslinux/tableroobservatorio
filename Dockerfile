# Imagen de la aplicación sistemabase (CodeIgniter 4 + PHP 8.1 + Apache)
FROM php:8.1-apache

# Paquetes del sistema necesarios para las extensiones de PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libxml2-dev \
        libonig-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        mysqli \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        xml \
        bcmath \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Apache: mod_rewrite para las URLs amigables y DocumentRoot apuntando a public/
RUN a2enmod rewrite headers \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Configuración de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/sistemabase.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Primero las dependencias (aprovecha la caché de capas de Docker)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# Código de la aplicación
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar \
    && chown -R www-data:www-data writable \
    && chmod -R 775 writable

# Script de arranque (ajusta permisos de writable/ al iniciar)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
