# 🚢 Sail-SSL

![Version](https://img.shields.io/github/v/release/ryoluo/sail-ssl)
![License](https://img.shields.io/github/license/ryoluo/sail-ssl)

Laravel Sail plugin to enable SSL (HTTPS) connection with Nginx.

## Install
You need to setup [Laravel Sail](https://github.com/laravel/sail) environment before using the plugin.

### With local PHP / Composer:
```sh
composer require ryoluo/sail-ssl --dev
php artisan sail-ssl:install
./vendor/bin/sail up
```

### With Sail container:
```sh
./vendor/bin/sail up -d
./vendor/bin/sail composer require ryoluo/sail-ssl --dev
./vendor/bin/sail artisan sail-ssl:install
./vendor/bin/sail down
./vendor/bin/sail up
```

## Configure Nginx
`./nginx/templates/default.conf.template` will be published.
```sh
php artisan sail-ssl:publish
```

## Contribution
Feel free to create a PR!
