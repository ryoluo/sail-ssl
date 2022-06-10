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
        $dockerCompose = file_get_contents($this->app->basePath('docker-compose.yml'));
        $nginxStub = file_get_contents('stubs/nginx.stub');
        $volumeStub = file_get_contents('stubs/volume.stub');
        $this->assertTrue(str_contains($dockerCompose, $nginxStub));
        $this->assertTrue(str_contains($dockerCompose, $volumeStub));
    }

    public function test_throw_exception_when_docker_compose_yml_is_not_found()
    {
        $this->expectException(\ErrorException::class);
        unlink($this->app->basePath('docker-compose.yml'));
        $this->artisan('sail-ssl:install')->assertFailed();
    }

    public function test_do_nothing_when_nginx_is_already_installed()
    {
        // First execution
        $this->artisan('sail-ssl:install')->assertSuccessful();
        $dockerComposeAfter1stExec = file_get_contents($this->app->basePath('docker-compose.yml'));
        // Execute again
        $this->artisan('sail-ssl:install')
            ->expectsOutput('Nginx container is already installed. Do nothing.')
            ->assertSuccessful();
        $dockerComposeAfter2ndExec = file_get_contents($this->app->basePath('docker-compose.yml'));
        $this->assertEquals($dockerComposeAfter1stExec, $dockerComposeAfter2ndExec);
    }
}
