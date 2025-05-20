# Use PHP 8.1 with FPM
FROM php:8.1-fpm

# Install system dependencies and PHP extensions needed by Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libonig-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory inside the container
WORKDIR /var/www

# Copy all backend files into the container
COPY . .

# Allow Composer to run as root (optional, but needed in Docker)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 9000 (default for php-fpm)
EXPOSE 9000

# Start php-fpm server
CMD ["php-fpm"]
