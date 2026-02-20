# ============================================================
# Production-Ready Dockerfile
# Stack: PHP 8.2 + Apache + Static Frontend
# Structure: /backend (PHP files) + /frontend (HTML/CSS/Images)
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Enable Apache mod_rewrite ──────────────────────────────
RUN a2enmod rewrite

# ── Apache Virtual Host Configuration ─────────────────────
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
    # Route /backend requests to PHP backend\n\
    Alias /backend /var/www/html/backend\n\
    <Directory /var/www/html/backend>\n\
        Options -Indexes\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# ── PHP Configuration (production tuned) ──────────────────
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i \
        -e 's/expose_php = On/expose_php = Off/' \
        -e 's/display_errors = On/display_errors = Off/' \
        -e 's/log_errors = Off/log_errors = On/' \
        -e 's/upload_max_filesize = 2M/upload_max_filesize = 10M/' \
        -e 's/post_max_size = 8M/post_max_size = 12M/' \
        -e 's/max_execution_time = 30/max_execution_time = 60/' \
        "$PHP_INI_DIR/php.ini"

# ── Set working directory ──────────────────────────────────
WORKDIR /var/www/html

# ── Copy project files ─────────────────────────────────────
# Backend PHP files
COPY backend/ ./backend/

# Frontend static files (HTML, CSS, images)
COPY frontend/ ./frontend/

# Copy index.html to web root so it loads by default
COPY frontend/index.html ./index.html

# ── .htaccess for clean routing ────────────────────────────
RUN echo 'Options -Indexes\n\
RewriteEngine On\n\
\n\
# Redirect root to index.html\n\
RewriteRule ^$ /index.html [L]\n\
\n\
# Serve frontend HTML files directly\n\
RewriteCond %{REQUEST_FILENAME} !-f\n\
RewriteCond %{REQUEST_FILENAME} !-d\n\
RewriteRule ^([a-zA-Z0-9_-]+)\.html$ /frontend/$1.html [L]\n\
\n\
# Block access to sensitive backend files directly\n\
RewriteRule ^backend/db\.php$ - [F,L]' > /var/www/html/.htaccess

# ── Ownership & permissions ────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# ── Expose HTTP port ───────────────────────────────────────
EXPOSE 80

# ── Health check ───────────────────────────────────────────
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Apache runs in foreground (default CMD from base image)
CMD ["apache2-foreground"]