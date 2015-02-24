FROM ubuntu:14.04

# Install dependencies
RUN apt-get update -y
RUN apt-get install -y git curl apache2 php5 libapache2-mod-php5 php5-mcrypt php5-mysql php5-gd php5-curl php-apc

# Install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer

# Install app
RUN rm -rf /var/www/*
ADD . /var/www
RUN  cd /var/www && /usr/bin/composer install

# Configure apache
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www
ADD apache.conf /etc/apache2/sites-available/000-default.conf
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# Configure WebStore
ADD parameters.yml /var/www/app/config/parameters.yml

EXPOSE 80

CMD /usr/sbin/apache2ctl -D FOREGROUND
