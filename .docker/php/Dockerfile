FROM php:8.2-fpm
RUN apt-get update \
    && apt-get install -y --no-install-recommends libssl-dev zlib1g-dev curl git unzip libxml2-dev libpq-dev libzip-dev \
    && pecl install apcu \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql  \
    && docker-php-ext-enable apcu pdo_pgsql sodium \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-source delete
WORKDIR /var/www/project
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install symfony-cli