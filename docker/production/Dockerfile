FROM php:7.4-apache

ARG SERVER_NAME
ARG LETS_ENCRYPT_EMAIL_ADDRESS

# Install certbot and the corresponding Apache plugin
RUN apt-get update && apt-get install -y certbot python3-certbot-apache

# Use the default production config file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install recommended PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mysqli gd imap intl apcu opcache

# Tell Apache what is the public server name
RUN echo "ServerName $SERVER_NAME" >> /etc/apache2/apache2.conf

# Enable Apache rewrite engine to make the API work
RUN a2enmod rewrite

# Copy the source code
COPY . /var/www/html/

# Initialize the config file
RUN mkdir -p /config
RUN cp --no-clobber \
    /var/www/html/include/ost-sampleconfig.php \
    /config/ost-config.php
RUN ln -sf /config/ost-config.php \
    /var/www/html/include/ost-config.php
RUN chown www-data: /config/ost-config.php

# Mark the directory of the config file as a volume to make sure it
# survives between container restarts
VOLUME /config

EXPOSE 80
