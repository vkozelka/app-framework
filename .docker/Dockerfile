FROM php:7.4-apache-buster

MAINTAINER Václav Kozelka

COPY . /srv/app
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN apt-get update
RUN apt-get install -y curl gnupg git

RUN apt-get update && apt-get install -y \
       libfreetype6-dev \
       libjpeg62-turbo-dev \
       libmcrypt-dev \
       libpng-dev \
       zlib1g-dev \
   && docker-php-ext-install -j$(nproc) iconv \
   && docker-php-ext-install zip \
   && docker-php-ext-install pdo_mysql \
   && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
   && docker-php-ext-install -j$(nproc) gd

RUN pecl install xdebug-2.6.1 \
   && docker-php-ext-enable xdebug

RUN pecl install mcrypt-1.0.1 \
   && docker-php-ext-enable mcrypt

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN chown -R www-data:www-data /srv/app && a2enmod rewrite
