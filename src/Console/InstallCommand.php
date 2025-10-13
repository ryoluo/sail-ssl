<?php

namespace Ryoluo\SailSsl\Console;

use Illuminate\Console\Command;

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

        $dockerCompose = file_get_contents($dockerComposePath);
        if (str_contains($dockerCompose, 'nginx:')) {
            $this->info('Nginx container is already installed. Do nothing.');
            return;
        }

        $nginxStub = file_get_contents(__DIR__ . '/../../stubs/nginx.stub');
        $volumeStub = file_get_contents(__DIR__ . '/../../stubs/volume.stub');
        $dockerCompose = preg_replace(
            ['/^services:/m', '/^volumes:/m'],
            ["services:\n{$nginxStub}", "volumes:\n{$volumeStub}"],
            $dockerCompose
        );
        file_put_contents($dockerComposePath, $dockerCompose);
        $this->info('Nginx container successfully installed in Docker Compose.');
    }
}
