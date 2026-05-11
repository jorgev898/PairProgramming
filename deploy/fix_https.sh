#!/bin/bash
# Comentar la línea que fuerza HTTPS
cd /var/www/pairprogramming
sed -i "s|URL::forceScheme('https');|// URL::forceScheme('https');|" app/Providers/AppServiceProvider.php

# Limpiar y recachear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar PHP
systemctl restart php8.2-fpm

echo "HTTPS fix applied!"
