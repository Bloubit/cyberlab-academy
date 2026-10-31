# Worker Dockerfile
FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    icu-dev \
    oniguruma-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Install Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Create worker user
RUN addgroup -g 1000 -S worker && \
    adduser -u 1000 -S worker -G worker

# Switch to worker user
USER worker

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php artisan queue:monitor default --max=100 || exit 1

# Queue worker configuration
ENV QUEUE_CONNECTION=redis
ENV QUEUE_DEFAULT_CONNECTION=redis
ENV QUEUE_WORKER_SLEEP=3
ENV QUEUE_WORKER_TRIES=3
ENV QUEUE_WORKER_MAX_TIME=3600

# Start queue worker
CMD ["php", "artisan", "queue:work", "--sleep=3", "--tries=3", "--max-time=3600", "--verbose"]
