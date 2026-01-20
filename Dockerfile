# =============================================================================
# Stage 1: Builder - PHP dependencies and frontend build
# =============================================================================
FROM php:8.2-fpm as builder

ARG UID=1000
ARG GID=1000
ARG NODE_VERSION=22.0.0

ENV USER=app
ENV HOME=/home/${USER}
ENV NVM_DIR=${HOME}/.nvm

WORKDIR /var/www/html

# Install build dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Create non-root user
RUN if ! getent group ${GID}; then groupadd -g ${GID} ${USER}; fi && \
    useradd -m -u ${UID} -g ${GID} -s /bin/bash ${USER}

# Switch to non-root user
USER ${USER}

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        intl \
        gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application code
COPY --chown=${USER}:${USER} . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Install NVM and Node.js
RUN mkdir -p ${NVM_DIR} && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.6/install.sh | bash && \
    bash -c ". ${NVM_DIR}/nvm.sh && nvm install ${NODE_VERSION} && nvm alias default ${NODE_VERSION}"

ENV PATH=${NVM_DIR}/versions/node/v${NODE_VERSION}/bin:$PATH

# Install and build frontend assets
RUN npm ci && npm run build

# =============================================================================
# Stage 2: Runtime - Minimal production image
# =============================================================================
FROM php:8.2-fpm

ARG UID=1000
ARG GID=1000

ENV USER=app
ENV HOME=/home/${USER}
ENV APP_ENV=production
ENV APP_DEBUG=false

WORKDIR /var/www/html

# Install only runtime dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng6 \
    libjpeg62-turbo \
    libfreetype6 \
    libonig5 \
    libxml2 \
    libzip4 \
    libicu72 \
    nginx \
    supervisor \
    curl \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Create non-root user with same UID/GID
RUN if ! getent group ${GID}; then groupadd -g ${GID} ${USER}; fi && \
    useradd -m -u ${UID} -g ${GID} -s /bin/bash ${USER}

# Copy PHP extensions from builder
COPY --from=builder /usr/local/lib/php/extensions/no-debug-non-zts-20220829/ \
    /usr/local/lib/php/extensions/no-debug-non-zts-20220829/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Copy application from builder
COPY --from=builder --chown=${USER}:${USER} /var/www/html /var/www/html

# Create required directories with proper permissions
RUN mkdir -p storage/logs bootstrap/cache && \
    chown -R ${USER}:${USER} storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy configuration files
COPY --chown=root:root docker/nginx.conf /etc/nginx/nginx.conf
COPY --chown=root:root docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY --chown=root:root docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --chown=root:root docker/scheduler.sh /usr/local/bin/scheduler.sh

RUN chmod +x /usr/local/bin/scheduler.sh

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Create non-root user for PHP-FPM
RUN mkdir -p /run/php && chown -R ${USER}:${USER} /run/php

# Expose ports
EXPOSE 80

# Use non-root user for application
USER ${USER}

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]