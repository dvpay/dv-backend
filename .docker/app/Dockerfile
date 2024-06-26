FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    wget \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    libgmp-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd sockets gmp

RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

RUN docker-php-ext-install opcache \
    && docker-php-ext-configure opcache --enable-opcache

RUN pecl install xdebug \
  && docker-php-ext-enable xdebug

ARG UID
ARG GID

RUN groupadd -g $GID www
RUN useradd -u $UID -ms /bin/bash -g www www

ENV DOCKERIZE_VERSION v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz

COPY local.ini /usr/local/etc/php/conf.d/local.ini

USER www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

