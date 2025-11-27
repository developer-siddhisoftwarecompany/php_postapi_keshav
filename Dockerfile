FROM php:8.2-apache

# Create a writable storage directory BEFORE copying the project
RUN mkdir -p /var/www/storage \
    && chown -R www-data:www-data /var/www/storage \
    && chmod -R 777 /var/www/storage

# Enable Apache rewrite
RUN a2enmod rewrite

# Set ServerName to prevent warnings
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy project files
COPY . /var/www/html/

# Ensure Apache user owns all content
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
