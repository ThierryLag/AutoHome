FROM php:7.0-cli

RUN apt-get update -y && apt-get install -y git

COPY . /opt/phpapp/
WORKDIR /opt/phpapp

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer update --prefer-source --no-interaction

CMD [ "php", "cli/cron.php" ]
