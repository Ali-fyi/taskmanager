#!/bin/sh
set -eu

php /var/www/html/artisan config:cache
php /var/www/html/artisan migrate --force

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf