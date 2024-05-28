# Use an official PHP runtime as a parent image
FROM php:8.3.7-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy your PHP application code into the container
COPY . .

# Remove the .git folder since it is huge
RUN rm -r .git

# Set permissions as stated here: https://manual.limesurvey.org/Installation_-_LimeSurvey_CE
RUN chmod -R 777 /var/www/html

# Install PHP extensions and other dependencies
RUN apt update && \
    apt install -y libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libldap2-dev libzip-dev libc-client-dev libkrb5-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install pdo pdo_mysql gd intl ldap zip imap && \
    apt clean && \
    rm -rf /var/lib/apt/lists/*

# Expose the port Apache listens on
EXPOSE 80

# Start Apache when the container runs
CMD ["apache2-foreground"]
