FROM phpdockerio/php:8.2-fpm

# Install PHP extensions
RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        git \
        php8.2-bcmath \
        php8.2-gd \
        php8.2-imagick \
        php8.2-imap \
        php8.2-mysql \
        php8.2-pgsql \
        php8.2-sqlite3; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Upgrade tzdata
RUN apt-get purge tzdata; \
    apt-get update; \
    apt-get upgrade -y tzdata

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR "/application"
