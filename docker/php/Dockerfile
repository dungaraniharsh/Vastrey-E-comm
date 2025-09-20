FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxslt1-dev \
    zlib1g-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd intl pdo_mysql zip soap xsl opcache

RUN pecl install xdebug-2.9.8 && docker-php-ext-enable xdebug || true

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN useradd -G www-data,root -u 1000 magento || true
RUN mkdir -p /var/www/html && chown -R magento:www-data /var/www/html

USER magento
