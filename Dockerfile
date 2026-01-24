# syntax=docker/dockerfile:1

FROM php:5.6-apache

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Fix Debian Stretch archived repositories (EOL)
RUN sed -i 's/deb.debian.org/archive.debian.org/g' /etc/apt/sources.list && \
    sed -i 's|security.debian.org|archive.debian.org|g' /etc/apt/sources.list && \
    sed -i '/stretch-updates/d' /etc/apt/sources.list

# Install required PHP extensions for MySQL, GD, and other dependencies
RUN apt-get update && apt-get install --allow-unauthenticated -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libmcrypt-dev \
    zlib1g-dev \
    mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    mcrypt \
    zip

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Increase PHP limits for large file uploads (200 MB)
RUN echo "upload_max_filesize = 500M" >> "$PHP_INI_DIR/php.ini" && \
    echo "post_max_size = 500M" >> "$PHP_INI_DIR/php.ini" && \
    echo "memory_limit = 512M" >> "$PHP_INI_DIR/php.ini" && \
    echo "max_execution_time = 300" >> "$PHP_INI_DIR/php.ini" && \
    echo "max_input_time = 300" >> "$PHP_INI_DIR/php.ini"

# Set Apache document root and enable .htaccess
RUN sed -i 's|/var/www/html|/var/www/html|g' /etc/apache2/sites-enabled/000-default.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Increase Apache LimitRequestBody for large file uploads (500 MB)
RUN echo 'LimitRequestBody 524288000' >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/files /var/www/html/tmp

# Run as www-data user
USER www-data
