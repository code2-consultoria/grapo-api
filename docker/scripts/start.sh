#!/bin/bash

# alias framework.
sudo ln -s /etc/nginx/sites/$FRAMEWORK.conf /etc/nginx/sites/enabled.conf

echo "listen mode ${NGINX_LISTEN_MODE}"

if [ "${NGINX_LISTEN_MODE}" = "nginx" ]; then
  # starts NGINX!
  echo "Iniciando nginx"
  nginx
fi

if [ "${NGINX_LISTEN_MODE}" = "fpm" ]; then
	# starts FPM
  echo "Iniciando"
  nohup php-fpm -F -O > /dev/stdout 2>&1 &
fi

if [ "${NGINX_LISTEN_MODE}" = "dual" ]; then
  # starts FPM
  echo "Iniciando php-fpm"
  nohup php-fpm -F -O > /dev/stdout 2>&1 &
  # starts NGINX!
  echo "Iniciando nginx"
  nginx
fi
