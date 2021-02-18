# Start work

## PHP
- PHP 8
### php.ini
- extension_dir = "ext"
- extension=openssl
- extension=mbstring
- extension=fileinfo
- extension=oci8_12c

## Create .env file
- copy .env.example to .env
- set password
- generate application key:

`php artisan key:generate`

## IDE helper
[barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper "github.com")

`php artisan ide-helper:generate`

`php artisan ide-helper:meta`

## Run artisan server

`php artisan serv --port=8000`
