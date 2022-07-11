<?php

namespace HumbleCore\App;

use Illuminate\Container\Container;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;

class Application extends Container
{
    const VERSION = '9.19.0';

    protected string $basePath;

    protected $appPath;

    protected $storagePath;

    protected $isRunningInConsole;

    public function __construct(?string $basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        Facade::setFacadeApplication($this);

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    public function setBasePath(string $basePath): self
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    protected function registerBaseBindings(): void
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);
    }

    protected function registerBaseServiceProviders(): void
    {
        //$this->register(new EventServiceProvider($this));
        //$this->register(new LogServiceProvider($this));
        //$this->register(new RoutingServiceProvider($this));
    }

    protected function bindPathsInContainer(): void
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
    }

    public function path(string $path = ''): string
    {
        $appPath = $this->appPath ?: $this->basePath.DIRECTORY_SEPARATOR.'app';

        return $appPath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function bootstrapPath(string $path = ''): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'bootstrap'.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function configPath(string $path = ''): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'config'.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function publicPath(): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'public';
    }

    public function storagePath(string $path = ''): string
    {
        return ($this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage')
                            .($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function resourcePath(string $path = ''): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'resources'.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function setStoragePath(string $path): self
    {
        $this->storagePath = $path;

        return $this;
    }

    public function isLocal(): bool
    {
        return $this['env'] === 'local';
    }

    public function runningUnitTests(): bool
    {
        return $this->bound('env') && $this['env'] === 'testing';
    }

    public function hasDebugModeEnabled(): bool
    {
        return (bool) $this['config']->get('app.debug');
    }

    public function runningInConsole(): bool
    {
        if ($this->isRunningInConsole === null) {
            $this->isRunningInConsole = Env::get('APP_RUNNING_IN_CONSOLE') ?? (\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg');
        }

        return $this->isRunningInConsole;
    }

    public function getNamespace(): string
    {
        return 'App';
    }
}
