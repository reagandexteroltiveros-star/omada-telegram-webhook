FROM php:8.2-apache

COPY . /var/www/html/
RUN a2enmod rewrite
WORKDIR /var/www/html/
EXPOSE 80
