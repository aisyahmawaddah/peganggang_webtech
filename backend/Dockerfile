# # Use the official PHP image
# FROM php:8.2-cli

# # Set working directory
# WORKDIR /app

# # Copy all backend files
# COPY . .

# # Expose the port Render expects
# EXPOSE 10000

# # Start the PHP built-in server
# CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
# Use official PHP image
FROM php:8.1-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
