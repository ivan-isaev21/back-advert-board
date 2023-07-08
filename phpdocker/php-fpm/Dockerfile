FROM phpdockerio/php:8.2-fpm
WORKDIR "/application"

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        git \ 
        php8.2-bcmath \ 
        php8.2-gd \ 
        php8.2-imagick \ 
        php8.2-imap \ 
        php8.2-mysql \ 
        php8.2-sqlite3 \
        php8.2-pgsql; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
