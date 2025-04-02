#!/bin/bash
cd /home3/abdinega/absensi.abdinegara.com/
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear
