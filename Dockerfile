FROM php:7.2.12-apache

RUN apt update && apt install -y \
    unzip \
    libzip-dev \
    libpq-dev \
    libldap2-dev \
    libc-client-dev \
    libkrb5-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
  && rm -rf /var/lib/apt/lists/*

RUN curl -o limesurvey.zip https://download.limesurvey.org/latest-stable-release/limesurvey3.15.5+181115.zip \
  && unzip limesurvey.zip \
  && mv limesurvey/* . \
  && rm -rf limesurvey limesurvey.zip \
  && chown -R www-data .

RUN docker-php-ext-install -j$(nproc) iconv \
  && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
  && docker-php-ext-install imap \
  && docker-php-ext-install pdo pdo_pgsql \
  && docker-php-ext-install zip \
  && docker-php-ext-install ldap

CMD ["apache2-foreground"]
