<?php

namespace Ryoluo\SailSsl\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sail-ssl:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the Nginx resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $dockerCompose = file_get_contents($this->laravel->basePath('docker-compose.yml'));
        $this->call('vendor:publish', ['--tag' => 'sail-ssl']);
        file_put_contents(
            $this->laravel->basePath('docker-compose.yml'),
            str_replace('./vendor/ryoluo/sail-ssl/nginx/templates', './nginx/templates', $dockerCompose)
        );
    }
}
