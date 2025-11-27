FROM php:8.2-apache

# Enable permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Create uploads directory with correct rights
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

# Copy project
COPY . /var/www/html/

# Enable Apache modules
RUN a2enmod rewrite

# Set ServerName to remove warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
