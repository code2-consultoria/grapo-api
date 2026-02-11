#!/usr/bin/env ash

echo "50-xdebug.sh revogado"

# if [[ $XDEBUG_ENABLED == true ]]; then
#     # enable xdebug extension
#     # sudo sed -i "/;zend_extension=xdebug/c\zend_extension=xdebug" /usr/local/etc/php/conf.d/00_xdebug.ini
#     echo "zend_extension=xdebug" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#
#     # enable xdebug remote config
#     echo "[xdebug]" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     # shellcheck disable=SC2006
#     echo "xdebug.client_host=host.docker.internal" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.client_port=${XDEBUG_PORT:-9003}" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.scream=0" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.cli_color=1" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.show_local_vars=1" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo 'xdebug.idekey = "'${XDEBUG_IDE_KEY:-code2}'"' | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.start_with_request=yes" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.mode=deb,debug" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
#     echo "xdebug.discover_client_host=true" | sudo tee -a /usr/local/etc/php/conf.d/00_xdebug.ini > /dev/null
# fi
