FROM php:8.3-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite and allow .htaccess overrides
RUN a2enmod rewrite \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Create application directory inside the container
WORKDIR /var/www/html/films_api

# Copy application source code into the container
COPY . /var/www/html/films_api

# Adjust DB host for Docker:
# change Config::HOST from 'localhost' to 'db' (the name of the DB service in docker-compose)
RUN sed -i "s/protected const HOST = 'localhost'/protected const HOST = 'db'/" classes/Config.php

# Ensure log directory exists and is writable by Apache (www-data)
RUN mkdir -p /var/www/html/films_api/log \
    && chown -R www-data:www-data /var/www/html/films_api \
    && chmod -R 775 /var/www/html/films_api/log

EXPOSE 80
