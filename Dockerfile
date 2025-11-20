# Use PHP + Apache
FROM php:8.2-apache

# Copy project files into web root
COPY . /var/www/html/

# Enable Apache rewrite module (optional)
RUN a2enmod rewrite
