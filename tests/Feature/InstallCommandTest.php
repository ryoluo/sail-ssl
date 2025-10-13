<?php

namespace Ryoluo\SailSsl\Tests\Feature;

use Ryoluo\SailSsl\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public function test_execute_install_command_successfully()
    {
        $this->artisan('sail-ssl:install')
            ->expectsOutput('Nginx container successfully installed in Docker Compose.')
            ->assertSuccessful();

        $dockerCompose = $this->app->basePath('docker-compose.yml');
        if (!file_exists($dockerCompose)) {
            $dockerCompose = $this->app->basePath('compose.yaml');
        }

        $dockerCompose = file_get_contents($dockerCompose);
        $nginxStub = file_get_contents('stubs/nginx.stub');
        $volumeStub = file_get_contents('stubs/volume.stub');
        $this->assertTrue(str_contains($dockerCompose, $nginxStub));
        $this->assertTrue(str_contains($dockerCompose, $volumeStub));
    }

    public function test_throw_exception_when_docker_compose_yml_is_not_found()
    {
        $this->expectException(\ErrorException::class);
        if (file_exists($this->app->basePath('docker-compose.yml'))) {
            unlink($this->app->basePath('docker-compose.yml'));
        } else if (file_exists($this->app->basePath('compose.yaml'))) {
            unlink($this->app->basePath('compose.yaml'));
        }
        $this->artisan('sail-ssl:install')->assertFailed();
    }

    public function test_do_nothing_when_nginx_is_already_installed()
    {
        // First execution
        $this->artisan('sail-ssl:install')->assertSuccessful();
        $dockerCompose = $this->app->basePath('docker-compose.yml');
        if (!file_exists($dockerCompose)) {
            $dockerCompose = $this->app->basePath('compose.yaml');
        }
        $dockerComposeAfter1stExec = file_get_contents($dockerCompose);
        // Execute again
        $this->artisan('sail-ssl:install')
            ->expectsOutput('Nginx container is already installed. Do nothing.')
            ->assertSuccessful();
        $dockerCompose = $this->app->basePath('docker-compose.yml');
        if (!file_exists($dockerCompose)) {
            $dockerCompose = $this->app->basePath('compose.yaml');
        }
        $dockerComposeAfter2ndExec = file_get_contents($dockerCompose);
        $this->assertEquals($dockerComposeAfter1stExec, $dockerComposeAfter2ndExec);
    }
}
