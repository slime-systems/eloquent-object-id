FROM php:8-cli

RUN apt-get update && apt-get install -y \
    catatonit \
    git \
    unzip \
    zip \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    curl \
    nano \
    wget \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

COPY --from=mirror.gcr.io/composer/composer:latest-bin /composer /usr/local/bin/composer

WORKDIR /var/www/html

STOPSIGNAL SIGINT

ENTRYPOINT ["catatonit", "-g", "--"]
CMD ["composer", "run", "test"]
