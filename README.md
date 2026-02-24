# Sail-SSL

![Version](https://img.shields.io/github/v/release/ryoluo/sail-ssl)
![Downloads](https://img.shields.io/packagist/dt/ryoluo/sail-ssl)
![License](https://img.shields.io/github/license/ryoluo/sail-ssl)
![Test](https://img.shields.io/github/actions/workflow/status/ryoluo/sail-ssl/laravel.yml?branch=main&label=test)

[Laravel Sail](https://github.com/laravel/sail) 環境で SSL（HTTPS）接続を簡単に有効化するプラグインです。Nginx リバースプロキシを利用して、自己署名証明書による HTTPS 通信をローカル開発環境で実現します。

## 目次

- [概要](#概要)
- [必要条件](#必要条件)
- [インストール](#インストール)
- [AppServiceProvider の設定](#appserviceprovider-の設定)
- [証明書の信頼設定（任意）](#証明書の信頼設定任意)
- [環境変数](#環境変数)
- [Nginx 設定のカスタマイズ](#nginx-設定のカスタマイズ)
- [トラブルシューティング](#トラブルシューティング)
- [コントリビューション](#コントリビューション)
- [ライセンス](#ライセンス)

## 概要

Sail-SSL は以下の仕組みでローカル開発環境に HTTPS を導入します。

```
ブラウザ → https://localhost:443 → Nginx（SSL終端） → http://laravel.test → Laravel アプリ
```

`sail-ssl:install` コマンドを実行すると、`docker-compose.yml` に Nginx コンテナが自動追加されます。コンテナ起動時に自己署名のルート CA 証明書とサーバー証明書が自動生成され、HTTPS でアクセスできるようになります。

## 必要条件

- [Laravel Sail](https://laravel.com/docs/sail) がセットアップ済みであること
- Docker および Docker Compose が利用可能であること

## インストール

### ローカルの PHP / Composer を使う場合

```sh
composer require ryoluo/sail-ssl --dev
php artisan sail-ssl:install
./vendor/bin/sail up
```

### Sail コンテナを使う場合

```sh
./vendor/bin/sail up -d
./vendor/bin/sail composer require ryoluo/sail-ssl --dev
./vendor/bin/sail artisan sail-ssl:install
./vendor/bin/sail down
./vendor/bin/sail up
```

インストール後、コンテナが起動すると https://localhost でアクセスできます。

> **補足:** `sail-ssl:install` コマンドは `docker-compose.yml`（または `compose.yaml`）に Nginx サービスを追加します。すでに `nginx` サービスが存在する場合はスキップされます。

## AppServiceProvider の設定

Nginx リバースプロキシの背後で動作するため、Laravel が正しく HTTPS の URL を生成するよう設定が必要です。`AppServiceProvider` の `boot` メソッドに以下を追加してください。

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    URL::forceScheme('https');
}
```

この設定がない場合、HTTPS でアクセスしていてもアセットやルートの URL が `http://` で生成されてしまいます。

## 証明書の信頼設定（任意）

プラグインはローカルのルート CA 証明書を生成し、それを使ってサーバー証明書に署名します。ルート CA 証明書をブラウザや OS に登録すると、セキュリティ警告を表示せずにアクセスできます。

### 1. ルート CA 証明書をホストマシンにコピー

```sh
./vendor/bin/sail cp nginx:/etc/nginx/certs/root-ca.crt .
```

### 2. 証明書をインポート

| OS / ブラウザ | 手順 |
|---|---|
| **Chrome** | 設定 > プライバシーとセキュリティ > セキュリティ > 証明書の管理 > 認証局 > インポート |
| **Firefox** | 設定 > プライバシーとセキュリティ > 証明書を表示 > 認証局 > インポート |
| **macOS** | `root-ca.crt` をダブルクリックしてキーチェーンアクセスで開き、「常に信頼」に設定 |
| **Windows** | `root-ca.crt` をダブルクリック > 証明書のインストール > 「信頼されたルート証明機関」に配置 |

> **注意:** `SSL_DOMAIN` や `SSL_ALT_NAME` を変更した場合は、Docker ボリュームを削除して証明書を再生成してください。
>
> ```sh
> docker volume rm sail-nginx
> ```

## 環境変数

`.env` ファイルで以下の環境変数を設定することで動作をカスタマイズできます。

| 変数名 | 説明 | デフォルト値 |
|---|---|---|
| `SERVER_NAME` | Nginx の `server_name` ディレクティブに設定される値 | `localhost` |
| `APP_SERVICE` | `docker-compose.yml` 内の Laravel コンテナのサービス名 | `laravel.test` |
| `HTTP_PORT` | Nginx の HTTP ポート（このポートへのリクエストは `SSL_PORT` にリダイレクトされます） | `8000` |
| `SSL_PORT` | Nginx の HTTPS ポート | `443` |
| `SSL_DOMAIN` | SSL 証明書の Common Name（例: `*.mydomain.test`）。`localhost` 以外のドメインを使用する場合に設定 | `localhost` |
| `SSL_ALT_NAME` | SSL 証明書の Subject Alternative Name（例: `DNS:localhost,DNS:mydomain.test`）。`localhost` 以外のドメインを使用する場合に設定 | `DNS:localhost` |

### カスタムドメインの使用例

`mydomain.test` でアクセスしたい場合、`.env` に以下を追加します。

```env
SERVER_NAME=mydomain.test
SSL_DOMAIN=mydomain.test
SSL_ALT_NAME=DNS:mydomain.test,DNS:localhost
```

また、`/etc/hosts`（macOS / Linux）または `C:\Windows\System32\drivers\etc\hosts`（Windows）に以下を追加してください。

```
127.0.0.1 mydomain.test
```

## Nginx 設定のカスタマイズ

デフォルトの Nginx 設定テンプレートをプロジェクトにコピーして編集できます。

```sh
php artisan sail-ssl:publish
```

このコマンドを実行すると、`./nginx/templates/default.conf.template` がプロジェクトルートに作成され、`docker-compose.yml` のボリュームマウントも自動的に更新されます。

テンプレートファイル内では `${SERVER_NAME}` や `${APP_SERVICE}` などの環境変数をそのまま使用できます。

## トラブルシューティング

### ポートが競合する場合

`SSL_PORT` や `HTTP_PORT` が他のサービスと競合する場合は、`.env` で変更できます。

```env
HTTP_PORT=8080
SSL_PORT=4443
```

### 証明書を再生成したい場合

Docker ボリュームを削除してコンテナを再起動してください。

```sh
docker volume rm sail-nginx
./vendor/bin/sail up
```

### アセットやリンクが HTTP で生成される場合

[AppServiceProvider の設定](#appserviceprovider-の設定) を確認してください。`URL::forceScheme('https')` が設定されていないと、HTTPS 環境でも HTTP の URL が生成されます。

## コントリビューション

プルリクエストは大歓迎です！バグ報告や機能リクエストは [Issues](https://github.com/ryoluo/sail-ssl/issues) からお願いします。

## ライセンス

[MIT License](LICENSE)
