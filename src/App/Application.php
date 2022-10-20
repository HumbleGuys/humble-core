<?php

namespace HumbleCore\App;

use HumbleCore\Support\Vite;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Whoops\Handler\Handler;

class Application extends Container
{
    protected string $basePath;

    protected string $templatePath;

    protected $appPath;

    protected $configPath;

    protected $publicPath;

    protected $storagePath;

    protected $resourcePath;

    protected $langPath;

    protected $isRunningInConsole;

    protected $booted = false;

    protected $hasBeenBootstrapped = false;

    protected $bootingCallbacks = [];

    protected $bootedCallbacks = [];

    protected $serviceProviders = [];

    protected $loadedProviders = [];

    public function __construct(?string $basePath, ?string $templatePath = null)
    {
        $this->registerErrorHandler();

        if ($templatePath) {
            $this->setTemplatePath($templatePath);
        }

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        Facade::setFacadeApplication($this);

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();

        $this->hasBeenBootstrapped = true;
    }

    public function init()
    {
        $this->registerServiceProviders();
    }

    public function registerErrorHandler()
    {
        if ($_ENV['APP_DEBUG'] == 'true') {
            $whoops = new \Whoops\Run;

            $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);

            $whoops->prependHandler(function () {
                // Hides sensible information of env isnt set to local
                if ($_ENV['APP_ENV'] !== 'local') {
                    $_ENV = [];
                    $_SERVER = [];
                }

                return Handler::DONE;
            });

            $whoops->register();
        } else {
            $whoops = new \Whoops\Run;

            $whoops->prependHandler(function ($exception) {
                logger()->error($exception->getMessage());

                return Handler::DONE;
            });

            $whoops->register();
        }
    }

    public function loadConfigFiles()
    {
        $configLoader = new ConfigLoader;

        $configLoader->loadConfig();
    }

    public function boot()
    {
        $request = Request::capture();
        $this->instance('request', $request);

        $this->bootProviders();

        $this->booted = true;
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

        $this->bind(
            'Illuminate\Foundation\Vite',
            function () {
                return new Vite;
            }
        );

        $this->singleton('cache', function ($app) {
            return new CacheManager($app);
        });

        $this->singleton(PackageManifest::class, function () {
            return new PackageManifest(
                new Filesystem, $this->templatePath(), $this->storagePath('cache/packages.php')
            );
        });

        $this->make(PackageManifest::class)->build();
    }

    protected function registerBaseServiceProviders(): void
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new LogServiceProvider($this));
        $this->register(new ValidationServiceProvider($this));
        $this->register(new TranslationServiceProvider($this));
    }

    protected function registerServiceProviders()
    {
        $providers = config('app.providers') ?? [];

        $providers = array_merge($providers, $this->make(PackageManifest::class)->providers() ?? []);

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
        $this->instance('path.lang', $this->langPath());
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

    public function templatePath(string $path = ''): string
    {
        return $this->templatePath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function configPath(string $path = ''): string
    {
        return ($this->configPath ?: $this->basePath.DIRECTORY_SEPARATOR.'config')
                            .($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function publicPath(string $path = ''): string
    {
        return ($this->publicPath ?: $this->basePath.DIRECTORY_SEPARATOR.'public_html')
                            .($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function storagePath(string $path = ''): string
    {
        return ($this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage')
                            .($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function resourcePath(string $path = ''): string
    {
        return ($this->resourcePath ?: $this->basePath.DIRECTORY_SEPARATOR.'resources')
                            .($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function langPath(string $path = ''): string
    {
        return ($this->langPath ?: $this->basePath.DIRECTORY_SEPARATOR.'lang')
                            .($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function setTemplatePath(string $path): self
    {
        $this->templatePath = $path;

        return $this;
    }

    public function setConfigPath(string $path): self
    {
        $this->configPath = $path;

        return $this;
    }

    public function setPublicPath(string $path): self
    {
        $this->publicPath = $path;

        return $this;
    }

    public function setStoragePath(string $path): self
    {
        $this->storagePath = $path;

        return $this;
    }

    public function setResourcePath(string $path): self
    {
        $this->resourcePath = $path;

        return $this;
    }

    public function setLangPath(string $path): self
    {
        $this->langPath = $path;

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
            'validator' => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
            'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    public function getLocale()
    {
        if (function_exists('get_bloginfo')) {
            return get_bloginfo('language');
        }
    }

    public function getFallbackLocale()
    {
        return 'en';
    }

    public function terminating()
    {
        // This function is needed
        // Add logic later if needed
    }
}
