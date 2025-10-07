FROM php:8.2-apache

WORKDIR /var/www/html

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Increase upload limits for PHP + Apache
RUN { \
  echo "upload_max_filesize = 200M"; \
  echo "post_max_size = 210M"; \
  echo "memory_limit = 512M"; \
  echo "max_execution_time = 120"; \
} > /usr/local/etc/php/conf.d/uploads.ini
# Apache limit (must match or exceed PHP)
RUN echo "LimitRequestBody 220000000" >> /etc/apache2/apache2.conf

# Copy Apache virtual host
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Copy Laravel app
COPY . /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
