#!/usr/bin/env ash

echo "98-composer.sh revogado"

# echo "bootstrap/cache"
# if [ ! -d "bootstrap/cache" ]; then
# sudo mkdir -p /var/www/app/bootstrap/cache
# sudo chown -R code2:code2 /var/www/app/bootstrap/cache
# sudo chmod -R ug+w /var/www/app/bootstrap/cache
# echo "bootstrap/cache criado"
# fi
#
# if [ ! -d "storage/framework/sessions" ]; then
# 	sudo mkdir -p /var/www/app/storage/framework/sessions
# 	sudo chown -R code2:code2 /var/www/app/storage
# 	sudo chmod -R ug+w /var/www/app/storage
# fi
#
# if [ "$APP_ENV" == "production" ]; then
# 	composer install --no-dev
# else
# 	composer install
# fi
#
# php artisan optimize
#
# # if [[ $PUBLISH_STORAGE == 1 ]]; then
# # php artisan storage:link
# # fi
