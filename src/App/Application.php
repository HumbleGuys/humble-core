<?php

namespace HumbleCore\App;

use HumbleCore\View\ViewServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

class Application extends Container
{
    const VERSION = '9.19.0';

    protected string $basePath;

    protected $appPath;

    protected $storagePath;

    protected $isRunningInConsole;

    protected $booted = false;

    protected $bootingCallbacks = [];

    protected $bootedCallbacks = [];

    protected $serviceProviders = [];

    protected $loadedProviders = [];

    public function __construct(?string $basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        Facade::setFacadeApplication($this);

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
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

        $this->instance('config', new Repository);

        $filesystem = new Filesystem;

        $this->bind('files', function ($app) use ($filesystem) {
            return $filesystem;
        });

        $this->instance(\Illuminate\Contracts\Foundation\Application::class, $this);

        $this->bind(
            'Illuminate\Contracts\Foundation\Application',
            function () {
                return $this;
            }
        );
    }

    protected function registerBaseServiceProviders(): void
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new ViewServiceProvider($this));
        //$this->register(new LogServiceProvider($this));
        //$this->register(new RoutingServiceProvider($this));
    }

    protected function registerServiceProviders()
    {
        $providers = config('app.providers') ?? [];

        //$providers = array_merge($providers, $this->make(PackageManifest::class)->providers() ?? []);

        if (empty($providers)) {
            return;
        }

        collect($providers)->each(function ($provider) {
            $this->register(new $provider($this));
        });
    }

    public function register($provider, $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->markAsRegistered($provider);

        return $provider;
    }

    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;

        $this->loadedProviders[get_class($provider)] = true;
    }

    protected function bootProviders()
    {
        if ($this->isBooted()) {
            return;
        }

        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($this);
        }
    }

    protected function bootProvider(ServiceProvider $provider)
    {
        $provider->callBootingCallbacks();

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $provider->callBootedCallbacks();
    }

    public function getProvider($provider)
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    protected function bindPathsInContainer(): void
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.resources', $this->resourcePath());
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

    public function isBooted()
    {
        return $this->booted;
    }

    public function getNamespace(): string
    {
        return 'App';
    }

    protected function registerCoreContainerAliases()
    {
        foreach ([
            'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
