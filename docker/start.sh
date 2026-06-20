#!/bin/bash
set -e
php /var/www/html/artisan migrate --force
exec apache2-foreground
