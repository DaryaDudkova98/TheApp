FROM webdevops/php-nginx-dev:8.3

WORKDIR /app

COPY . /app

RUN git config --global --add safe.directory /app

ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php
