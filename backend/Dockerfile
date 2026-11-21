FROM php:8.2-cli-alpine

# Install PDO and MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

COPY . .

# Expose artisan serve port
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
