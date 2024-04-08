#!/usr/bin/env bash
set -e
cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
if [[ ${XDEBUG_ENABLE} == true ]]
then

  pecl install xdebug
  cp /home/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

  if [[ ${XDEBUG_CLIENT_HOST} != "" ]]
    then sed -i -e 's/xdebug.client_host = localhost/xdebug.client_host = '"${XDEBUG_CLIENT_HOST}"'/g' /usr/local/etc/php/conf.d/xdebug.ini
  fi

  if [[ ${XDEBUG_CLIENT_PORT} != "" ]]
    then sed -i -e 's/xdebug.client_port = 9003/xdebug.client_port = '"${XDEBUG_CLIENT_PORT}"'/g' /usr/local/etc/php/conf.d/xdebug.ini
  fi

  if [[ ${XDEBUG_MODE} != "" ]]
    then sed -i -e 's/xdebug.mode = debug/xdebug.mode = '"${XDEBUG_MODE}"'/g' /usr/local/etc/php/conf.d/xdebug.ini
  fi

  if [[ ${XDEBUG_IDE_KEY} != "" ]]
    then sed -i -e 's/xdebug.idekey = docker/xdebug.idekey = '"${XDEBUG_IDE_KEY}"'/g' /usr/local/etc/php/conf.d/xdebug.ini
  fi
fi

php-fpm