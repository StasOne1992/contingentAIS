FROM php:8.2-fpm
RUN apt-get update
RUN apt-get install --reinstall ca-certificates
RUN apt-get install -y --no-install-recommends  apt-utils supervisor systemctl  libssl-dev zlib1g-dev  \
    curl git unzip libxml2-dev libpq-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev
RUN pecl install apcu \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql gd \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-enable apcu pdo_pgsql sodium \
    && docker-php-ext-configure zip \
    && docker-php-source delete


WORKDIR /var/www/html/web-app
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install symfony-cli


## start SuperVisor service

RUN /bin/sh -c 'systemctl enable supervisor.service'
