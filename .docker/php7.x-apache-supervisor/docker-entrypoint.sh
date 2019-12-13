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
       echo "Any code that you need to execute in container starting procces" 
       echo "*************************"
       echo "******  Your code  ******"
       echo "*************************"
fi

exec "$@"