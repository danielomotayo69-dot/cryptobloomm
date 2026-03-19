# Use official PHP 8.2 Apache image
FROM php:8.2-apache

# Install PHP extensions required for Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Force Apache to use a single MPM (prefork) and enable rewrite
RUN a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Fix permissions for storage and bootstrap/cache
RUN mkdir -p storage framework/cache bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache framework \
    && chmod -R 775 storage bootstrap/cache framework

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
