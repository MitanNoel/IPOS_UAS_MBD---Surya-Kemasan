FROM php:8.1-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    default-libmysqlclient-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite and other required modules
RUN a2enmod rewrite headers

# Configure Apache to listen on port 8080 (non-privileged for rootless)
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true

# Set Apache document root to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Create non-root user
RUN groupadd -r appuser && useradd -r -g appuser appuser

# Copy project files
COPY --chown=appuser:appuser . /var/www/html/

# Set proper permissions (755 for dirs, 644 for files)
RUN find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Ensure Apache can write to necessary directories
RUN mkdir -p /var/run/apache2 && chmod 755 /var/run/apache2 && chown appuser:appuser /var/run/apache2
RUN mkdir -p /var/log/apache2 && chown appuser:appuser /var/log/apache2 && chmod 755 /var/log/apache2

# Configure Apache to run as appuser
RUN sed -i 's/www-data/appuser/g' /etc/apache2/envvars

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh && chown appuser:appuser /usr/local/bin/docker-entrypoint.sh

# Create .env directory with proper permissions
RUN chown appuser:appuser /var/www/html

# Switch to non-root user
USER appuser

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
