#!/usr/bin/env bash

cp .env.example .env
cp .env.example.testing .env.testing
composer install
chmod -R 777 storage
php artisan key:generate
php artisan cache:clear
php artisan migrate
php-fpm
