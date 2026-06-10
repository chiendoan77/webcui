FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    ca-certificates \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/

WORKDIR /var/www/html/

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
