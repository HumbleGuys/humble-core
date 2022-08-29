<?php

use HumbleCore\App\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Response;

if (! function_exists('app')) {
    function app(?string $abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('asset')) {
    function asset(?string $path = null): string
    {
        return get_template_directory_uri().'/resources/'.$path;
    }
}

if (! function_exists('basePath')) {
    function basePath(string $string = ''): string
    {
        return app()->basePath($string);
    }
}

if (! function_exists('templatePath')) {
    function templatePath(string $string = ''): string
    {
        return app()->templatePath($string);
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

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * @return mixed|\Illuminate\Cache\CacheManager
     *
     * @throws \InvalidArgumentException
     */
    function cache()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return app('cache');
        }

        if (is_string($arguments[0])) {
            return app('cache')->get(...$arguments);
        }

        if (! is_array($arguments[0])) {
            throw new InvalidArgumentException(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
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

if (! function_exists('info')) {
    /**
     * Write some information to the log.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    function info($message, $context = [])
    {
        app('log')->info($message, $context);
    }
}

if (! function_exists('logger')) {
    /**
     * Log a debug message to the logs.
     *
     * @param  string|null  $message
     * @param  array  $context
     * @return \Illuminate\Log\LogManager|null
     */
    function logger($message = null, array $context = [])
    {
        if (is_null($message)) {
            return app('log');
        }

        return app('log')->debug($message, $context);
    }
}

if (! function_exists('menu')) {
    function menu(?string $name)
    {
        if (empty($name)) {
            return app('menu');
        }

        return app('menu')->get($name);
    }
}

if (! function_exists('svg')) {
    function svg(string $name, string $class = '')
    {
        $svg = file_get_contents(resourcePath('assets/images/'.$name.'.svg'));

        if (empty($class)) {
            return $svg;
        }

        $doc = new \DOMDocument;
        $doc->loadXML($svg);

        foreach ($doc->getElementsByTagName('svg') as $item) {
            $item->setAttribute('class', $class);
        }

        return $doc->saveHTML();
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  \Illuminate\Contracts\View\View|string|array|null  $content
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }
}

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Http\Request|string|array|null
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        $value = app('request')->__get($key);

        return is_null($value) ? value($default) : $value;
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
