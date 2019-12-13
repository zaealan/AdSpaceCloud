#!/bin/sh

set -e

# Prepend environemt variables to the crontab
env |cat - /etc/cron.d/crontab > /tmp/crontab
mv /tmp/crontab /etc/cron.d/crontab

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- supervisord "$@"
fi

if [ "$1" = 'supervisord' ]; then
    if [ ! -d "public/uploads" ]; then
        mkdir -p public/uploads
    fi

    if [ ! -d "var/cache" ]; then             
        mkdir var/cache
    fi

    if [ ! -d "var/log" ]; then             
        mkdir var/log              
    fi
    chgrp -R www-data var/cache var/log
    chmod -R g+w var/cache var/log

    #composer install --no-interaction

    #php bin/console d:s:u --force

fi

exec "$@"