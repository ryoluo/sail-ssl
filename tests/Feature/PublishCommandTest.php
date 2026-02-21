<?php

namespace Ryoluo\SailSsl\Tests\Feature;

use Ryoluo\SailSsl\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class PublishCommandTest extends TestCase
{
    public function test_execute_publish_command_successfully()
    {
        $this->artisan('sail-ssl:install')->assertSuccessful();
        $this->artisan('sail-ssl:publish')->assertSuccessful();

        $dockerComposePath = $this->app->basePath('docker-compose.yml');
        if (!file_exists($dockerComposePath)) {
            $dockerComposePath = $this->app->basePath('compose.yaml');
        }

        $dockerCompose = Yaml::parseFile($dockerComposePath);
        $this->assertContains('./nginx/templates:/etc/nginx/templates', $dockerCompose['services']['nginx']['volumes']);
        $this->assertTrue(file_exists($this->app->basePath('nginx/templates/default.conf.template')));
    }

    public function test_throw_exception_when_docker_compose_yml_is_not_found()
    {
        $this->expectException(\Symfony\Component\Yaml\Exception\ParseException::class);
        if (file_exists($this->app->basePath('docker-compose.yml'))) {
            unlink($this->app->basePath('docker-compose.yml'));
        } else if (file_exists($this->app->basePath('compose.yaml'))) {
            unlink($this->app->basePath('compose.yaml'));
        }
        $this->artisan('sail-ssl:publish')->assertFailed();
    }
}
