# Mirrors a typical IONOS Linux Web Hosting stack: Apache + PHP + MySQL
FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite headers
# Allow .htaccess overrides (needed for the no-cache rules during UAT)
RUN sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
# Document root is the project folder (bind-mounted via docker-compose)
