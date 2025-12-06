FROM webdevops/php-nginx:8.3

WORKDIR /app

COPY . /app

RUN git config --global --add safe.directory /app

RUN composer install --no-dev --optimize-autoloader
RUN php bin/console doctrine:migrations:migrate --no-interaction

ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php

EXPOSE 80
