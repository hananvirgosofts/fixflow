FROM dunglas/frankenphp:1-php8.4-alpine
WORKDIR /app

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && apk add --no-cache ca-certificates \
    && install-php-extensions pdo_mysql gd

COPY Caddyfile /etc/caddy/Caddyfile
COPY . /app

RUN mkdir -p uploads storage \
    && chmod 777 uploads storage

ENV PORT=80 \
    FIXFLOW_SESSION=database

EXPOSE 80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
