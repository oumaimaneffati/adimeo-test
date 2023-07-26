FROM php:8.2-fpm

RUN apt-get update && apt-get -y install git mariadb-client wget unzip


# install the PHP extensions we need
RUN apt-get install -y libpng-dev libjpeg-dev libpq-dev zlib1g-dev libonig-dev libzip-dev \
#	&& apt-get install -y wkhtmltopdf openssl build-essential xorg libssl-dev \ # to install wkhtmltopdf we need to suppress menu to choose keyboard layout
	&& rm -rf /var/lib/apt/lists/* \
	&& docker-php-ext-configure gd --with-jpeg \
	&&  docker-php-ext-install gd mbstring pdo pdo_mysql pdo_pgsql zip bcmath
# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN set -ex; \
\
# Drush
composer global require drush/drush:^11.0; \
\
# Drush launcher
wget -O drush.phar \
"https://github.com/drush-ops/drush-launcher/releases/download/0.10.2/drush.phar"; \
chmod +x drush.phar; \
mv drush.phar /usr/local/bin/drush; \
\
# Drupal console
curl https://drupalconsole.com/installer -L -o drupal.phar; \
mv drupal.phar /usr/local/bin/drupal; \
chmod +x /usr/local/bin/drupal; 

WORKDIR /var/www/html

