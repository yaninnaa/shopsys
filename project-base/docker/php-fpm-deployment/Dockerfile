FROM php:7.2-fpm-alpine

# install git for computing diffs
RUN apk add --update git

# install Composer
# path is relative to project root dir because this image is build by CI from root dir
COPY project-base/docker/php-fpm/docker-install-composer /usr/local/bin/docker-install-composer
RUN chmod +x /usr/local/bin/docker-install-composer && docker-install-composer

# libpng-dev needed by "gd" extension
# icu-dev needed by "intl" extension
# postgresql-dev needed by "pgsql" extension
# libzip-dev needed by "zip" extension
# autoconf needed by "redis" extension
# freetype-dev needed by "gd" extension
# libjpeg-turbo-dev needed by "gd" extension
RUN apk add --update \
    libpng-dev \
    icu-dev \
    postgresql-dev \
    libzip-dev \
    autoconf \
    freetype-dev \
    libjpeg-turbo-dev

# "zip" extension warns about deprecation if we do not use a system library
RUN docker-php-ext-configure zip --with-libzip

# "gd" extension needs to have specified jpeg and freetype dir for jpg/jpeg images support
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

# install necessary PHP extensions requested by Composer
RUN docker-php-ext-install \
    bcmath \
    gd \
    intl \
    opcache \
    pgsql \
    pdo_pgsql \
    zip

# redis PHP extension is not provided with the PHP source and must be installed via PECL, build-base used only for installation
RUN apk add --update build-base && pecl install redis-4.0.2 && docker-php-ext-enable redis && apk del build-base

# install npm
RUN apk add --update nodejs-npm

# install grunt-cli using npm to be able to run grunt watch
RUN npm install -g grunt-cli

# install postgresql to allow execution of pg_dump for acceptance tests
RUN apk add --update --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.7/main postgresql

# install locales and switch to en_US.utf8 in order to enable UTF-8 support
# see https://github.com/docker-library/php/issues/240#issuecomment-305038173
RUN apk add --update --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/testing gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
ENV LC_ALL=en_US.utf8 LANG=en_US.utf8 LANGUAGE=en_US.utf8

# overwrite the original entry-point from the PHP Docker image with our own
COPY project-base/docker/php-fpm/docker-php-entrypoint /usr/local/bin/
#
## allow overwriting UID and GID o the user "www-data" to help solve issues with permissions in mounted volumes
## if the GID is already in use, we will assign GID 82 instead (82 is the standard uid/gid for "www-data" in Alpine)
#ARG www_data_uid
#ARG www_data_gid
#RUN deluser www-data && (addgroup -g $www_data_gid www-data || addgroup -g 82 www-data) && adduser -u $www_data_uid -D -S -G www-data www-data
#USER www-data

ADD / /var/www/html

WORKDIR /var/www/html

ARG composer_github_oauth_token

RUN composer config -g github-oauth.github.com $composer_github_oauth_token
RUN composer global require hirak/prestissimo
RUN composer install --optimize-autoloader --no-interaction --no-progress
