#!/bin/bash
composer install

php artisan key:generate

php artisan migrate

php artisan passport:install

echo "Execute main:"
docker-php-entrypoint $@
echo "Main Done"

php-fpm -F

exec $@