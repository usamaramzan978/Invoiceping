#!/bin/bash

# Laravel Scheduler Runner
# Runs the Laravel scheduler every minute

set -e

cd /var/www/html

while true; do
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done