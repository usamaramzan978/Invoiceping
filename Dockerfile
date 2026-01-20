# -----------------------------
# Base Image: PHP 8.2 FPM
# -----------------------------
FROM php:8.2-fpm

# -----------------------------
# Set working directory
# -----------------------------
WORKDIR /var/www/html

# -----------------------------
# Install system dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    supervisor \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Install PHP extensions
# -----------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        intl \
        gd

# -----------------------------
# Install Composer
# -----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------
# Install Node.js (for Vite / React)
# -----------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# -----------------------------
# Copy application files
# -----------------------------
COPY . .

# -----------------------------
# Set permissions for Laravel
# -----------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# -----------------------------
# Install PHP dependencies
# -----------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# -----------------------------
# Install & build frontend assets
# -----------------------------
RUN npm install && npm run build

# -----------------------------
# Copy Nginx & Supervisor configs
# -----------------------------
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/scheduler.sh /scheduler.sh
RUN chmod +x /scheduler.sh

# -----------------------------
# Expose HTTP port
# -----------------------------
EXPOSE 80

# -----------------------------
# Start Supervisor (Nginx + PHP-FPM)
# -----------------------------
CMD ["/usr/bin/supervisord", "-n"]
