FROM php:8.1-fpm

# Install system deps and PHP extensions commonly needed
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring zip gd

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app directory
WORKDIR /var/www/html

# Set user and permissions
RUN useradd -G www-data,root -u 1000 -m developer
RUN chown -R developer:developer /var/www/html

# Switch to non-root user
USER developer

EXPOSE 9000

CMD ["php-fpm"]
