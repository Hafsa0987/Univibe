FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY . /var/www/html/
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf && sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf && apache2-foreground
