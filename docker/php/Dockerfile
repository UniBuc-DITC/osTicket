FROM php:7.4-apache

# Install recommended PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mysqli gd imap intl apcu opcache

# Enable Apache rewrite engine to make the API work
RUN a2enmod rewrite

# Install the Xdebug extension for debugging support
RUN install-php-extensions xdebug
