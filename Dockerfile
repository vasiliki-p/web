FROM php:8.1-apache

# Εγκατάσταση των απαραίτητων επεκτάσεων για τη σύνδεση με τη βάση δεδομένων MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Ενεργοποίηση του mod_rewrite του Apache
RUN a2enmod rewrite

RUN ln -s /etc/secrets/connection.php /var/www/html/connection.php

# Αντιγραφή όλων των αρχείων του project σου (από το GitHub) στον server του Render
COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads
    
EXPOSE 80
