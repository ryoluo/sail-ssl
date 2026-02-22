# 🚢 Sail-SSL

![Version](https://img.shields.io/github/v/release/ryoluo/sail-ssl)
![Downloads](https://img.shields.io/packagist/dt/ryoluo/sail-ssl)
![License](https://img.shields.io/github/license/ryoluo/sail-ssl)
![Test](https://img.shields.io/github/actions/workflow/status/ryoluo/sail-ssl/laravel.yml?branch=main&label=test)

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

After containers started, you can access https://localhost.

## Update AppServiceProvider

Since the application is behind an Nginx reverse proxy that handles SSL, Laravel needs to be configured to generate HTTPS URLs. Add `URL::forceScheme('https')` to your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

Without this setting, Laravel may generate HTTP URLs for assets, routes, etc., even though the site is served over HTTPS.

## Trust the certificate (optional)

The plugin generates a local Root CA certificate to sign the server certificate.
You can import the Root CA into your browser to remove the security warning.

### 1. Copy the Root CA certificate to your host machine:

```sh
./vendor/bin/sail cp nginx:/etc/nginx/certs/root-ca.crt .
```

### 2. Import the certificate:

-   **Chrome**: Settings > Privacy and Security > Security > Manage certificates > Authorities > Import
-   **Firefox**: Settings > Privacy & Security > Security > View Certificates > Authorities > Import
-   **macOS**: Double-click the `root-ca.crt` file to open Keychain Access, then set "Always Trust"

> **Note:** If you change `SSL_DOMAIN` or `SSL_ALT_NAME`, remove the Docker volume `sail-nginx` to regenerate certificates:
> ```sh
> docker volume rm sail-nginx
> ```

## Environment variables

-   `SERVER_NAME`
    -   Determine `server_name` directive in nginx.conf
    -   Default: `localhost`
-   `APP_SERVICE`
    -   Specify Laravel container name in docker-compose.yml
    -   Default: `laravel.test`
-   `HTTP_PORT`
    -   Port to forward Nginx HTTP port
    -   By default, request for this port would redirect to `SSL_PORT`
    -   Default: `8000`
-   `SSL_PORT`
    -   Port to forward Nginx HTTPS port
    -   Default: `443`
-   `SSL_DOMAIN`
    -   The Common Name to use in the SSL certificate, e.g. `SSL_DOMAIN=*.mydomain.test`
    -   Required to generate a valid certificate for a domain other than `localhost`
    -   Default: `localhost`
-   `SSL_ALT_NAME`
    -   The Subject Alternative Name to use in the SSL certificate, e.g. `SSL_ALT_NAME=DNS:localhost,DNS:mydomain.test`
    -   Required to generate a valid certificate for a domain other than `localhost`
    -   Default: `DNS:localhost`

## Configure Nginx

`./nginx/templates/default.conf.template` will be published.

```sh
php artisan sail-ssl:publish
```

## Contribution

Feel free to create a PR!
