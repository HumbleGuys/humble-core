<?php

use HumbleCore\App\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;

if (! function_exists('app')) {
    function app(?string $abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('basePath')) {
    function basePath(string $string = ''): string
    {
        return app()->basePath($string);
    }
}

if (! function_exists('configPath')) {
    function configPath(string $string = ''): string
    {
        return app()->configPath($string);
    }
}

if (! function_exists('publicPath')) {
    function publicPath(string $string = ''): string
    {
        return app()->publicPath($string);
    }
}

if (! function_exists('storagePath')) {
    function storagePath(string $string = ''): string
    {
        return app()->storagePath($string);
    }
}

if (! function_exists('resourcePath')) {
    function resourcePath(string $string = ''): string
    {
        return app()->resourcePath($string);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     */
    function config(string|array $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('view')) {
    function view(?string $view = null, array $data = [], array $mergeData = []): string
    {
        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData)->render();
    }
}
