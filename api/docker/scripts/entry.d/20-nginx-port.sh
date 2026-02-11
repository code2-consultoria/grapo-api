#!/usr/bin/env ash

# Comment out HTTP/HTTPS listener

# if [[ "${NGINX_MODE}" = "https" ]]; then
# 	echo "NGINX_MODE https"
# 	sudo sed -i 's/^\(listen[][ :]*8083.*\)/#\1/g' /etc/nginx/sites/common.conf
# else
# 	echo "NGINX_MODE http"
# 	sudo sed -i 's/^\(listen[][ :]*8080.*\)/#\1/g' /etc/nginx/sites/common.conf
# fi

# replace HTTP port.
# sudo sed -i "s/enabled\.conf/${FRAMEWORK}/g" /etc/nginx/nginx.conf

echo "replace http port ${NGINX_HTTP_PORT}"
sudo sed -i "s/.*listen 8080/listen ${NGINX_HTTP_PORT}/g" /etc/nginx/sites/common.conf
sudo sed -i "s/.*listen \[::\]:8080/listen \[::\]:${NGINX_HTTP_PORT}/g" /etc/nginx/sites/common.conf
# replace HTTPS pot.
sudo sed -i "s/8083/${NGINX_HTTPS_PORT}/g" /etc/nginx/sites/common.conf

# replace backend host and port.
sudo sed -i "s/NGINX_FPM_BACKEND_HOST/${NGINX_FPM_BACKEND_HOST}/g" /etc/nginx/sites/locations/laravel.conf
sudo sed -i "s/NGINX_FPM_BACKEND_PORT/${NGINX_FPM_BACKEND_PORT}/g" /etc/nginx/sites/locations/laravel.conf
sudo sed -i "s/NGINX_FPM_BACKEND_HOST/${NGINX_FPM_BACKEND_HOST}/g" /etc/nginx/sites/locations/symfony.conf
sudo sed -i "s/NGINX_FPM_BACKEND_PORT/${NGINX_FPM_BACKEND_PORT}/g" /etc/nginx/sites/locations/symfony.conf

sudo sed -i "s/listen = .*/listen = ${NGINX_FPM_BACKEND_PORT}/g" /usr/local/etc/php-fpm.conf