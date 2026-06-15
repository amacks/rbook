# ---------------------------------------------------------------------------
# rbook — PHP recipe management app
# Base: php:8.2-apache (Apache 2.4 + PHP 8.2 as mod_php)
# ---------------------------------------------------------------------------
FROM php:8.2-apache

# Install system packages needed by PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-configure gd \
          --with-freetype \
          --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
          pdo_mysql \
          mbstring \
          gd \
    && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite (required for dispatch.php URL routing via .htaccess)
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Copy application source
# ---------------------------------------------------------------------------
WORKDIR /var/www/html

# Copy dependency manifests first so Docker layer cache isn't busted
# by unrelated source changes
COPY composer.json composer.lock ./

# Install PHP dependencies (production — no dev packages)
RUN composer install --no-dev --optimize-autoloader --no-interaction -d rbook

# Copy the rest of the application
COPY . rbook

# Remove the default Apache vhost and install ours
RUN rm -f /etc/apache2/sites-enabled/000-default.conf
COPY docker/rbook.conf /etc/apache2/sites-enabled/rbook.conf

# The entrypoint generates config.php at runtime from environment variables
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ensure www-data can write to the Smarty compile cache directory.
# The actual directory is created at runtime by entrypoint.sh in case
# a different skin is configured via the SKIN env var.
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


