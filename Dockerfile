# --- Node build stage ---
FROM node:20 AS nodebuild
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- PHP/Nginx stage ---
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    nginx \
    supervisor \
    libpng libpng-dev \
    libjpeg-turbo libjpeg-turbo-dev \
    libwebp libwebp-dev \
    freetype freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    zip \
    unzip \
    curl \
    git


# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd


# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/
# Copy application files (except node_modules and public assets)
COPY . .

# Copy built assets from nodebuild
COPY --from=nodebuild /app/public /var/www/public
COPY --from=nodebuild /app/node_modules /var/www/node_modules

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/ \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Nginx config for Laravel
RUN mkdir -p /run/nginx
COPY docker/nginx/vhost.conf /etc/nginx/conf.d/erp_backed_apis.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Create Supervisor log directory
RUN mkdir -p /var/log/supervisord \
    && chown -R www-data:www-data /var/log/supervisord

# Supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Expose HTTP port
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"] 