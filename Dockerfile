FROM php:8.2-apache

# Instalar dependencias necesarias para PostgreSQL y otras utilidades
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copiar tu código fuente
COPY . /var/www/html/

# Dar permisos correctos (opcional si necesitás)
RUN chown -R www-data:www-data /var/www/html
