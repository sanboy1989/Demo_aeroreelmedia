# Mirrors a typical IONOS Linux Web Hosting stack: Apache + PHP + MySQL
FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite headers
# Allow .htaccess overrides (needed for the no-cache rules during UAT)
RUN sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
# Allow larger uploads so background videos can be uploaded
RUN { echo 'upload_max_filesize=128M'; echo 'post_max_size=130M'; echo 'max_execution_time=300'; } > /usr/local/etc/php/conf.d/uploads.ini
# Document root is the project folder (bind-mounted via docker-compose)
