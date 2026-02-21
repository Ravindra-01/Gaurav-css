# ============================================================
# Production-Ready Dockerfile
# Stack: PHP 8.2 + Apache + Composer
# ============================================================

FROM php:8.2-apache

# ── System dependencies & PHP extensions ──────────────────
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Install Composer ───────────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ── Enable Apache modules ──────────────────────────────────
RUN a2enmod rewrite

# ── Apache Virtual Host ────────────────────────────────────
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    DirectoryIndex index.html index.php\n\
\n\
    <Directory /var/www/html>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
\n\
    <Directory /var/www/html/backend>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        php_flag engine on\n\
    </Directory>\n\
\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# ── PHP ini (production) ───────────────────────────────────
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i \
        -e 's/expose_php = On/expose_php = Off/' \
        -e 's/display_errors = On/display_errors = Off/' \
        -e 's/log_errors = Off/log_errors = On/' \
        -e 's/upload_max_filesize = 2M/upload_max_filesize = 10M/' \
        -e 's/post_max_size = 8M/post_max_size = 12M/' \
        -e 's/max_execution_time = 30/max_execution_time = 60/' \
        "$PHP_INI_DIR/php.ini"

# ── Working directory ──────────────────────────────────────
WORKDIR /var/www/html

# ── Install Composer dependencies ──────────────────────────
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ── Copy entire frontend folder contents to web root ───────
# Copies everything inside frontend/ directly into /var/www/html/
# so CSS, images, and HTML all work with their existing paths
COPY frontend/ ./

# ── Copy backend PHP files ─────────────────────────────────
COPY backend/ ./backend/

# ── Copy .env (local Docker only) ─────────────────────────
COPY .env* ./

# ── Root .htaccess ─────────────────────────────────────────
RUN printf 'Options -Indexes\nRewriteEngine On\n\n# Block direct db.php access\nRewriteRule ^backend/db\\.php$ - [F,L]\n' \
    > /var/www/html/.htaccess

# ── Backend .htaccess ──────────────────────────────────────
RUN printf 'Options -Indexes\nAddHandler application/x-httpd-php .php\n' \
    > /var/www/html/backend/.htaccess

# ── Fix ownership & permissions ────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# ── Expose port ────────────────────────────────────────────
EXPOSE 80

# ── Health check ───────────────────────────────────────────
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

CMD ["apache2-foreground"]