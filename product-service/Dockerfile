FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    librabbitmq-dev \
    libssl-dev \
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

# PHP-FPM: listen on TCP (more reliable than unix socket in containers)
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf

# Nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
