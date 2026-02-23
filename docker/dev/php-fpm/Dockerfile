# Uses php:8.3-fpm, installs system dependencies (git, zip, etc.),
# installs required PHP extensions (pdo_mysql, gd, etc.), and sets up Composer
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the rest of your application code
COPY . /var/www/html

# Copy only the composer files first to leverage Docker cache
#COPY composer.json composer.lock ./

# Run composer install
# remove '--no-dev' flag, so able to run 'php artisan test'
RUN /usr/bin/composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Set appropriate permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# RUN database migration
RUN php artisan migrate

# Expose the default Laravel port
EXPOSE 8000

# Start the Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
