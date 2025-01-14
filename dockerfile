# Use an official PHP image as a base
FROM php:8.1-apache

# Install required PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Set permissions
RUN mkdir -p /var/www/html/upload /var/www/html/application/config /var/www/html/tmp \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/tmp \
    && chmod -R 755 /var/www/html/upload \
    && chmod -R 755 /var/www/html/application/config

# Expose the web server port
EXPOSE 80

# Set the default command
CMD ["apache2-foreground"]
