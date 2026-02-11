#!/usr/bin/env ash

# Set PHP memory limit value.
sudo sed -i "/memory_limit = .*/c\memory_limit = $PHP_MEMORY_LIMIT" /usr/local/etc/php-fpm.conf
sudo sed -i "/extension_dir = .*/c\memory_limit = /etc/php81/conf.d" /usr/local/etc/php-fpm.conf