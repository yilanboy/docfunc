#!/usr/bin/env ash
set -e

initialStuff() {
    php artisan optimize:clear
    php artisan package:discover --ansi
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
}

initialStuff

php artisan octane:start --server=swoole --host=0.0.0.0 --port=9000 --workers=auto --task-workers=auto --max-requests=500

