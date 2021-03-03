### DEPENDENCIES

- [PHP version 8](https://www.php.net/releases/8.0/en.php)
- [Laravel](https://laravel.com/docs/8.x)
  - Laravel-OCI8 is an Oracle Database Driver: 
    [github](https://github.com/yajra/laravel-oci8) 
    [doc](https://yajrabox.com/docs/laravel-oci8/master)
- Javascript
  - [Vue 3](https://v3.vuejs.org/)
  - [Vue UI Component Library](https://primefaces.org/primevue)

### PHP.INI
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
