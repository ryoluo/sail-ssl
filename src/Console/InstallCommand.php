<?php

namespace Ryoluo\SailSsl\Console;

use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sail-ssl:install';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Nginx container in Docker Compose';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $dockerComposePath = $this->laravel->basePath('docker-compose.yml');
        if (!file_exists($dockerComposePath)) {
            $dockerComposePath = $this->laravel->basePath('compose.yaml');
        }

        $dockerCompose = Yaml::parseFile($dockerComposePath);
        if (isset($dockerCompose['services']['nginx'])) {
            $this->info('Nginx container is already installed. Do nothing.');
            return;
        }

        $dockerCompose['services']['nginx'] = [
            'image' => 'nginx:latest',
            'ports' => [
                '${HTTP_PORT:-8000}:80',
                '${SSL_PORT:-443}:443',
            ],
            'environment' => [
                'SSL_PORT=${SSL_PORT:-443}',
                'APP_SERVICE=${APP_SERVICE:-laravel.test}',
                'SERVER_NAME=${SERVER_NAME:-localhost}',
                'SSL_DOMAIN=${SSL_DOMAIN:-localhost}',
                'SSL_ALT_NAME=${SSL_ALT_NAME:-DNS:localhost}',
            ],
            'volumes' => [
                'sail-nginx:/etc/nginx/certs',
                './vendor/ryoluo/sail-ssl/nginx/templates:/etc/nginx/templates',
                './vendor/ryoluo/sail-ssl/nginx/generate-ssl-cert.sh:/docker-entrypoint.d/99-generate-ssl-cert.sh',
            ],
            'depends_on' => [
                '${APP_SERVICE:-laravel.test}',
            ],
            'networks' => [
                'sail',
            ],
        ];

        if (!isset($dockerCompose['volumes'])) {
            $dockerCompose['volumes'] = [];
        }
        $dockerCompose['volumes']['sail-nginx'] = [
            'driver' => 'local',
        ];

        file_put_contents($dockerComposePath, Yaml::dump($dockerCompose, 10, 4));
        $this->info('Nginx container successfully installed in Docker Compose.');
    }
}
