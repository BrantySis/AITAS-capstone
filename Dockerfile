# -------------------------
# 1. Base Image: PHP 8.4 with Apache
# -------------------------
FROM php:8.4-apache

# -------------------------
# 2. Install System Dependencies and PHP Extensions
# -------------------------
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
    && docker-php-ext-install gd pdo pdo_mysql zip mbstring bcmath xml opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -------------------------
# 3. Enable Apache rewrite and configure ports & document root
# -------------------------
RUN a2enmod rewrite && \
    sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf && \
    echo "<Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>" >> /etc/apache2/apache2.conf

# -------------------------
# 4. Set working directory
# -------------------------
WORKDIR /var/www/html

# -------------------------
# 5. Install Node.js and NPM for Vite build
# -------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Copy package files first to cache dependencies
COPY package*.json ./
RUN npm install

# -------------------------
# 6. Copy Laravel files
# -------------------------
COPY . .

# -------------------------
# 7. Build frontend (Vite)
# -------------------------
RUN npm run build

# -------------------------
# 8. Install Composer dependencies
# -------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# -------------------------
# 9. Set permissions
# -------------------------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# -------------------------
# 10. Copy entrypoint script
# -------------------------
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# -------------------------
# 11. Expose & Run
# -------------------------
EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]
