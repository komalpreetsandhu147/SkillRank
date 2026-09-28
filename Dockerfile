FROM php:8.2-apache

# Install MySQL and PDO extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Copy and configure entrypoint script with Unix LF line endings and executable permissions
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh && chmod 755 /usr/local/bin/docker-entrypoint.sh

# Ensure proper permissions for Apache web server
RUN chown -R www-data:www-data /var/www/html

# Run through /bin/sh to guarantee cross-platform execution compatibility
ENTRYPOINT ["/bin/sh", "/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
