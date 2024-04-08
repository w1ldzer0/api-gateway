FROM php:8.1-fpm
ARG ENV
WORKDIR /var/www

RUN apt-get update && \
    apt-get install -y --no-install-recommends libssl-dev zlib1g-dev curl git unzip netcat libxml2-dev libpq-dev libzip-dev && \
    pecl install apcu && \
    pecl install redis && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql && \
    docker-php-ext-enable apcu pdo_pgsql sodium redis&& \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD ./docker/php/conf.d/additional.ini /usr/local/etc/php/conf.d/custom.ini
ADD ./docker/php/conf.d/xdebug.ini /home/xdebug.ini
ADD ./docker/php/xdebug-entrypoint.sh /home/xdebug-entrypoint


RUN chmod +x /home/xdebug-entrypoint

ADD . /var/www

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN echo $ENV
RUN if [ "$ENV" = "dev" ]; then \
  composer install --prefer-dist --no-scripts --no-interaction; else \
  composer install --no-dev --prefer-dist --no-scripts --no-interaction; \
    fi


RUN mkdir -p /var/www/var/cache /var/www/var/log
RUN chown -R www-data:www-data /var/www/var

ENTRYPOINT ["/home/xdebug-entrypoint"]