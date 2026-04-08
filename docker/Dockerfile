FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    librabbitmq-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    wait-for-it \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql intl zip opcache \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP-FPM config: listen on unix socket
RUN sed -i 's|listen = 127.0.0.1:9000|listen = /run/php/php-fpm.sock|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.owner = www-data|listen.owner = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.group = www-data|listen.group = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && mkdir -p /run/php

# Copy nginx config
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/sites-enabled/default.bak 2>/dev/null || true

# Copy supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
