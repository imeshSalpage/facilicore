FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    sqlite-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
