# Devcontainer Dockerfile - extends the main Dockerfile with development tools
FROM php:5.6-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
	&& docker-php-ext-install pdo pdo_mysql mysqli

# Install additional development tools
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    nano \
    vim \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy app files
COPY . /var/www/html

# Set up Apache to use port 80 (which gets exposed as 8080 via compose)
RUN a2enmod rewrite

# Create logs directory if it doesn't exist
RUN mkdir -p /var/www/html/logs

USER www-data
