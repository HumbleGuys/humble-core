<?php

declare(strict_types=1);

namespace Tests;

use HumbleCore\App\Application;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ?Application $app = null;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'false';
    }

    /**
     * @param  array<class-string>  $providers
     * @param  array<string, mixed>  $config
     */
    protected function bootApplication(array $providers = [], array $config = []): Application
    {
        $this->app = new Application(dirname(__DIR__), dirname(__DIR__));

        $storagePath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/storage';

        if (! is_dir($storagePath.'/cache')) {
            mkdir($storagePath.'/cache', 0777, true);
        }

        $this->app->setStoragePath($storagePath);

        config(array_replace_recursive([
            'app' => [
                'providers' => $providers,
            ],
        ], $config));

        $this->app->init();
        $this->app->boot();

        return $this->app;
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();

        if ($this->app !== null) {
            $this->app->flush();
            $this->app = null;
        }

        Application::setInstance();
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }
}
