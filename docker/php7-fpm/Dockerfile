FROM php:7.2-fpm

RUN apt-get update && apt-get install -y \
    apt-transport-https \
    openssl \
    git \
    unzip \
    gnupg

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Install yarn, Node.js
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get update && apt-get install -y \
    yarn \
    nodejs

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini
RUN "date"

# Type docker-php-ext-install to see available extensions
RUN docker-php-ext-install pdo pdo_mysql opcache exif

# Install GD
RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
     && docker-php-ext-configure gd \
          --enable-gd-native-ttf \
          --with-freetype-dir=/usr/include/freetype2 \
          --with-png-dir=/usr/include \
          --with-jpeg-dir=/usr/include \
    && docker-php-ext-install gd \
    && docker-php-ext-enable gd

RUN apt-get install -y \
        zlib1g-dev \
    && docker-php-ext-install zip

RUN apt-get -y update
RUN apt-get -y upgrade
RUN apt-get install -y ffmpeg


RUN apt-get update && apt-get install -y -q git rake ruby-ronn zlib1g-dev && apt-get clean

RUN pecl install grpc

#install protoc
RUN mkdir -p /tmp/protoc && \
    curl -L https://github.com/google/protobuf/releases/download/v3.2.0/protoc-3.2.0-linux-x86_64.zip > /tmp/protoc/protoc.zip && \
    cd /tmp/protoc && \
    unzip protoc.zip && \
    cp /tmp/protoc/bin/protoc /usr/local/bin && \
    cd /tmp && \
    rm -r /tmp/protoc && \
    docker-php-ext-enable grpc

RUN docker-php-ext-install bcmath

COPY php.ini /usr/local/etc/php/

RUN echo 'alias sf="php bin/console"' >> ~/.bashrc

RUN set -xe && \
  groupadd -g 1000 -o -f user && \
  useradd --shell /bin/bash -u 1000 -g user -o -c "" -m user


WORKDIR /var/www/symfony
