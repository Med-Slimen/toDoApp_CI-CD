FROM php:8.3-cli-alpine

# PDO MySQL driver + Composer
RUN docker-php-ext-install pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-interaction --optimize-autoloader
COPY public ./public
COPY src ./src

# Run as non-root user
RUN adduser -D app
USER app

EXPOSE 8000
HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
  CMD wget -qO- http://127.0.0.1:8000/health > /dev/null || exit 1

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
