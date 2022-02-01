FROM php:7.4-apache

ARG SERVER_NAME
ARG LETS_ENCRYPT_EMAIL_ADDRESS

# Install cron
RUN apt-get update && apt-get -y install cron

# Install certbot and the corresponding Apache plugin
RUN apt-get install -y certbot python3-certbot-apache

# Set up a certificate auto renewal job
RUN crontab -l | { cat; echo '0 12 * * * /usr/bin/certbot renew --quiet'; } | crontab -

# Use the default production config file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install recommended PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mysqli gd imap intl apcu opcache

# Tell Apache what is the public server name
RUN echo "ServerName $SERVER_NAME" >> /etc/apache2/apache2.conf

# Enable TLS module
RUN a2enmod ssl

# Enable Apache rewrite engine to make the API work
RUN a2enmod rewrite

# Enable Apache headers module, for HSTS
RUN a2enmod headers

# Turn on HSTS
RUN echo '\
Header always set Strict-Transport-Security "max-age=15552000; includeSubDomains"\
' >> /etc/apache2/apache2.conf

# Copy the source code
COPY . /var/www/html/

# Copy the .htaccess config file
COPY ./docker/production/htaccess /var/www/html/.htaccess

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
EXPOSE 443
