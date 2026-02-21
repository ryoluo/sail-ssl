<?php

namespace Ryoluo\SailSsl\Tests\Feature;

use Ryoluo\SailSsl\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class InstallCommandTest extends TestCase
{
    public function test_execute_install_command_successfully()
    {
        $this->artisan('sail-ssl:install')
            ->expectsOutput('Nginx container successfully installed in Docker Compose.')
            ->assertSuccessful();

        $dockerComposePath = $this->app->basePath('docker-compose.yml');
        if (!file_exists($dockerComposePath)) {
            $dockerComposePath = $this->app->basePath('compose.yaml');
        }

        $dockerCompose = Yaml::parseFile($dockerComposePath);
        $this->assertArrayHasKey('nginx', $dockerCompose['services']);
        $this->assertEquals('nginx:latest', $dockerCompose['services']['nginx']['image']);
        $this->assertContains('${HTTP_PORT:-8000}:80', $dockerCompose['services']['nginx']['ports']);
        $this->assertContains('${SSL_PORT:-443}:443', $dockerCompose['services']['nginx']['ports']);
        $this->assertContains('sail-nginx:/etc/nginx/certs', $dockerCompose['services']['nginx']['volumes']);
        $this->assertArrayHasKey('sail-nginx', $dockerCompose['volumes']);
        $this->assertEquals('local', $dockerCompose['volumes']['sail-nginx']['driver']);
    }

    public function test_throw_exception_when_docker_compose_yml_is_not_found()
    {
        $this->expectException(\Symfony\Component\Yaml\Exception\ParseException::class);
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
