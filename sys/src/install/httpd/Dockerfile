FROM php:8.1-apache

RUN apt-get update

RUN apt-get install -y libbz2-dev mc
RUN apt-get install -y libc-client-dev libkrb5-dev
RUN apt-get install -y libldap2-dev
# RUN apt-get install -y libfreetype6-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libgmp-dev
RUN apt-get install -y libtidy-dev
RUN apt-get install -y libxslt-dev
RUN apt-get install -y libzip-dev
RUN apt-get install -y libicu-dev
# RUN apt-get install -y libmcrypt-dev
RUN apt-get install -y libmemcached-dev zlib1g-dev
RUN apt-get install -y libmagickwand-dev --no-install-recommends
RUN apt-get install -y libxml2-dev && apt-get clean -y

RUN pecl install -o -f redis &&  rm -rf /tmp/pear &&  docker-php-ext-enable redis

RUN docker-php-ext-install soap
RUN pecl install memcached-3.1.4 && docker-php-ext-enable memcached
# RUN pecl install mcrypt-1.0.4 && docker-php-ext-enable mcrypt

RUN pecl install imagick && docker-php-ext-enable imagick && rm -rf /var/lib/apt/lists/*

# RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu
RUN docker-php-ext-install ldap

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
RUN docker-php-ext-install imap

RUN docker-php-ext-install zip
RUN docker-php-ext-install gmp
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install bz2
RUN docker-php-ext-install calendar
RUN docker-php-ext-install exif
RUN docker-php-ext-install gettext
RUN docker-php-ext-install sysvsem
RUN docker-php-ext-install sysvshm
RUN docker-php-ext-install sockets
RUN docker-php-ext-install shmop
RUN docker-php-ext-install tidy
RUN docker-php-ext-install xsl
# RUN docker-php-ext-install wddx
# RUN docker-php-ext-install xmlrpc
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN pecl install xdebug && docker-php-ext-enable xdebug
# COPY ioncube_loader_lin_7.3.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/ioncube_loader_lin_7.3.so
# COPY 00-ioncube.ini /usr/local/etc/php/conf.d/00-ioncube.ini

RUN pecl install mongodb
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini
RUN docker-php-ext-install -j$(nproc) opcache
RUN echo 'ServerName localhost:80' >> /etc/apache2/apache2.conf
RUN a2enmod rewrite

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /var/www/site.zone/htdocs/www
COPY default.conf /etc/apache2/sites-available/000-default.conf
