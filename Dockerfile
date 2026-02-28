# Dockerfile for PHP Webhook Application

FROM php:7.4-apache

# Install required packages
RUN apt-get update && apt-get install -y libzip-dev unzip && docker-php-ext-install zip

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy the application source code to the container
COPY . .

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Expose port 80
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]
