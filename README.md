# 🚢 Sail-SSL
Laravel Sail plugin to enable SSL (HTTPS) connection with Nginx.

## Install
You need to setup [Laravel Sail](https://github.com/laravel/sail) environment before using this plugin.

### With local PHP / Composer:
```sh
composer require ryoluo/sail-ssl
php artisan sail-ssl:install
./vendor/bin/sail up
```

### With Sail container:
```sh
./vendor/bin/sail up -d
./vendor/bin/sail composer require ryoluo/sail-ssl
./vendor/bin/sail artisan sail-ssl:install
./vendor/bin/sail down
./vendor/bin/sail up
```

## Configure nginx.conf
`./nginx/templates/default.conf.template` will be published.
```sh
php artisan sail-ssl:publish
```
