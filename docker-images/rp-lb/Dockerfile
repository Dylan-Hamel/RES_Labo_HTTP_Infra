FROM php:7.0-apache

RUN apt-get update && \       
        apt-get install -y vim 

COPY apache2-foreground /usr/local/bin/
COPY conf/ /etc/apache2
COPY template/ /var/apache2/template/


RUN a2enmod proxy proxy_http proxy_balancer
RUN a2ensite 000-* 001-*
