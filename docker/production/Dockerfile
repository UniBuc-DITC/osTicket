FROM php:7.4-apache

# Use the default production config file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install recommended PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mysqli gd imap intl apcu opcache

# Enable Apache rewrite engine to make the API work
RUN a2enmod rewrite

# Copy the source code
COPY . /var/www/html/

# Initialize the config file
RUN mkdir -p /config
RUN cp --no-clobber \
    /var/www/html/include/ost-sampleconfig.php \
    /config/ost-config.php
RUN ln -sf /config/ost-config \
    /var/www/html/include/ost-config.php

# Mark the directory of the config file as a volume to make sure it \
# survives between container restarts
VOLUME /config

EXPOSE 80
