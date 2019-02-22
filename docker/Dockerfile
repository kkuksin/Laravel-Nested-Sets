FROM php:7.2.2-fpm

RUN apt-get update && apt-get install -y mysql-client --no-install-recommends \
 && docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN echo 'alias cept="./vendor/bin/codecept"' >> ~/.bashrc