# Use an official PHP runtime as a parent image
FROM php:8.3.7-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy your PHP application code into the container
COPY . .

# Remove the .git folder since it is huge
RUN rm -r .git

RUN ls -al
# Set permissions as stated here: https://manual.limesurvey.org/Installation_-_LimeSurvey_CE
RUN chmod -R 777 /var/www/html

# Install PHP extensions and other dependencies
RUN apt update
#    apt install -y libpng-dev && \
#    docker-php-ext-install pdo pdo_mysql gd

# Expose the port Apache listens on
EXPOSE 80

# Start Apache when the container runs
CMD ["apache2-foreground"]
