# Use PHP with Apache
FROM php:8.2-apache

# Copy app files to container
COPY . /var/www/html/

# Enable Apache Rewrite module (optional for routing)
RUN a2enmod rewrite
