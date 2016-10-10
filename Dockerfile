FROM php:7.0-cli
MAINTAINER Thierry 'akarun' Lagasse <akarun@passtech.be>

RUN apt-get update -y && apt-get install -y git zip unzip

RUN docker-php-ext-install sockets mbstring mysqli pdo pdo_mysql

COPY . /opt/phpapp/
WORKDIR /opt/phpapp

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer update --prefer-source --no-interaction

ONBUILD RUN composer install
CMD [ "php", "cli/cron.php" ]
