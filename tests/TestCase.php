<?php

namespace Ryoluo\SailSsl\Tests;

use Ryoluo\SailSsl\SailSslServiceProvider;
use Laravel\Sail\SailServiceProvider;
use Illuminate\Support\Facades\Artisan;

class TestCase extends \Orchestra\Testbench\TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->generateFiles();
        Artisan::call('sail:install', ['--with' => 'mysql']);
    }

    private function generateFiles()
    {
        $env = fopen($this->app->basePath('.env'), 'w');
        fclose($env);
        $phpunit = fopen($this->app->basePath('phpunit.xml'), 'w');
        fclose($phpunit);
    }

    protected function getPackageProviders($app)
    {
        return [
            SailServiceProvider::class,
            SailSslServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        $this->deleteFiles();
        parent::tearDown();
    }

    private function deleteFiles()
    {
        unlink($this->app->basePath('.env'));
        unlink($this->app->basePath('phpunit.xml'));
        if (file_exists($this->app->basePath('docker-compose.yml'))) {
            unlink($this->app->basePath('docker-compose.yml'));
        } else if (file_exists($this->app->basePath('compose.yaml'))) {
            unlink($this->app->basePath('compose.yaml'));
        }
        if (file_exists($this->app->basePath('nginx/templates/default.conf.template'))) {
            unlink($this->app->basePath('nginx/templates/default.conf.template'));
            rmdir($this->app->basePath('nginx/templates'));
            rmdir($this->app->basePath('nginx'));
        }
    }
}
