# Use PHP 8.4 with Apache
FROM php:8.4-apache

# Install PHP extensions and dependencies required for Laravel
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        zip \
        unzip \
        git \
        curl \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip mbstring bcmath xml opcache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory inside container
WORKDIR /var/www/html

# Copy project files to container
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Set storage permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Set Apache DocumentRoot to Laravel public folder
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Keep Apache running in foreground
CMD ["apache2-foreground"]

# Expose port 80
EXPOSE 80
