# Sail-SSL

![Version](https://img.shields.io/github/v/release/ryoluo/sail-ssl)
![Downloads](https://img.shields.io/packagist/dt/ryoluo/sail-ssl)
![License](https://img.shields.io/github/license/ryoluo/sail-ssl)
![Test](https://img.shields.io/github/actions/workflow/status/ryoluo/sail-ssl/laravel.yml?branch=main&label=test)

A [Laravel Sail](https://github.com/laravel/sail) plugin that enables SSL (HTTPS) for your local development environment using an Nginx reverse proxy with self-signed certificates.

## Table of Contents

- [How It Works](#how-it-works)
- [Requirements](#requirements)
- [Installation](#installation)
- [Update AppServiceProvider](#update-appserviceprovider)
- [Trust the Certificate (Optional)](#trust-the-certificate-optional)
- [Environment Variables](#environment-variables)
- [Customize Nginx Configuration](#customize-nginx-configuration)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## How It Works

Sail-SSL adds an Nginx container to your Docker Compose setup that acts as an SSL-terminating reverse proxy:

```
Browser → https://localhost:443 → Nginx (SSL termination) → http://laravel.test → Laravel App
```

Running the `sail-ssl:install` command automatically adds an Nginx service to your `docker-compose.yml`. On container startup, a self-signed Root CA and server certificate are generated, enabling HTTPS access out of the box.

## Requirements

- [Laravel Sail](https://laravel.com/docs/sail) already set up in your project
- Docker and Docker Compose installed

## Installation

### With Local PHP / Composer

```sh
composer require ryoluo/sail-ssl --dev
php artisan sail-ssl:install
./vendor/bin/sail up
```

### With Sail Container

```sh
./vendor/bin/sail up -d
./vendor/bin/sail composer require ryoluo/sail-ssl --dev
./vendor/bin/sail artisan sail-ssl:install
./vendor/bin/sail down
./vendor/bin/sail up
```

Once the containers are running, you can access https://localhost.

> **Note:** The `sail-ssl:install` command adds an Nginx service to your `docker-compose.yml` (or `compose.yaml`). If an `nginx` service already exists, the command will be skipped.

## Update AppServiceProvider

Since the application runs behind an Nginx reverse proxy that handles SSL, Laravel needs to be configured to generate HTTPS URLs. Add `URL::forceScheme('https')` to the `boot` method of your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    URL::forceScheme('https');
}
```

Without this setting, Laravel may generate HTTP URLs for assets, routes, etc., even though the site is served over HTTPS.

## Trust the Certificate (Optional)

The plugin generates a local Root CA certificate to sign the server certificate. You can import the Root CA into your browser or OS to remove the security warning.

### 1. Copy the Root CA certificate to your host machine

```sh
./vendor/bin/sail cp nginx:/etc/nginx/certs/root-ca.crt .
```

### 2. Import the certificate

| OS / Browser | Steps |
|---|---|
| **Chrome** | Settings > Privacy and Security > Security > Manage certificates > Authorities > Import |
| **Firefox** | Settings > Privacy & Security > Security > View Certificates > Authorities > Import |
| **macOS** | Double-click `root-ca.crt` to open Keychain Access, then set "Always Trust" |
| **Windows** | Double-click `root-ca.crt` > Install Certificate > Place in "Trusted Root Certification Authorities" |

> **Note:** If you change `SSL_DOMAIN` or `SSL_ALT_NAME`, remove the Docker volume to regenerate certificates:
>
> ```sh
> docker volume rm sail-nginx
> ```

## Environment Variables

You can customize the behavior by setting the following environment variables in your `.env` file:

| Variable | Description | Default |
|---|---|---|
| `SERVER_NAME` | Value for the `server_name` directive in nginx.conf | `localhost` |
| `APP_SERVICE` | Laravel service name defined in docker-compose.yml | `laravel.test` |
| `HTTP_PORT` | Nginx HTTP port (requests to this port are redirected to `SSL_PORT`) | `8000` |
| `SSL_PORT` | Nginx HTTPS port | `443` |
| `SSL_DOMAIN` | Common Name (CN) for the SSL certificate (e.g. `*.mydomain.test`). Set this when using a domain other than `localhost` | `localhost` |
| `SSL_ALT_NAME` | Subject Alternative Name (SAN) for the SSL certificate (e.g. `DNS:localhost,DNS:mydomain.test`). Set this when using a domain other than `localhost` | `DNS:localhost` |

### Custom Domain Example

To access your app via `mydomain.test`, add the following to your `.env`:

```env
SERVER_NAME=mydomain.test
SSL_DOMAIN=mydomain.test
SSL_ALT_NAME=DNS:mydomain.test,DNS:localhost
```

Then add an entry to your hosts file (`/etc/hosts` on macOS/Linux, `C:\Windows\System32\drivers\etc\hosts` on Windows):

```
127.0.0.1 mydomain.test
```

## Customize Nginx Configuration

You can publish the default Nginx configuration template to your project for customization:

```sh
php artisan sail-ssl:publish
```

This copies `default.conf.template` to `./nginx/templates/` in your project root and automatically updates the volume mount in `docker-compose.yml`.

You can use environment variables such as `${SERVER_NAME}` and `${APP_SERVICE}` directly in the template file.

## Troubleshooting

### Port Conflicts

If `SSL_PORT` or `HTTP_PORT` conflicts with another service, change them in your `.env`:

```env
HTTP_PORT=8080
SSL_PORT=4443
```

### Regenerate Certificates

Remove the Docker volume and restart the containers:

```sh
docker volume rm sail-nginx
./vendor/bin/sail up
```

### Assets or Links Generated with HTTP

Make sure you have configured `URL::forceScheme('https')` in your `AppServiceProvider`. See [Update AppServiceProvider](#update-appserviceprovider) for details.

## Contributing

Pull requests are welcome! For bug reports and feature requests, please open an [Issue](https://github.com/ryoluo/sail-ssl/issues).

## License

[MIT License](LICENSE)
