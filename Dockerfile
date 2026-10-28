# Use PHP with Apache
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libcurl4-openssl-dev libzip-dev \
    && docker-php-ext-install mysqli pdo_mysql mbstring curl zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Copy app files to container
COPY . /var/www/html/
COPY docker/php.ini /usr/local/etc/php/conf.d/simplr.ini
RUN mkdir -p /var/www/html/storage/attachments \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod 700 /var/www/html/storage /var/www/html/storage/attachments

# Enable Apache Rewrite module (optional for routing)
RUN printf '<Directory /var/www/html>\nAllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/simplr.conf && a2enconf simplr
