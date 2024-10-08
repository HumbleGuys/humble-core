<?php

use HumbleCore\App\Application;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

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
        if (! function_exists('get_template_directory_uri')) {
            return '/resources/'.$path; // temp fix
        }

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
    function config(string|array|null $key = null, mixed $default = null): mixed
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

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     * @param  string  $name
     * @return mixed
     */
    function resolve($name, array $parameters = [])
    {
        return app($name, $parameters);
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param  \Illuminate\Contracts\View\View|string|array|null  $content
     * @param  int  $status
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

if (! function_exists('route')) {
    function route(string $name, mixed $key = null, ?array $query = null): string
    {
        $route = app('router')->getRoute($name);

        $url = $route->url($key);

        if (! empty($query)) {
            $url .= '?'.Arr::query($query);
        }

        return $url;
    }
}

if (! function_exists('to_route')) {
    function to_route(string $name, mixed $key = null, ?array $query = null): void
    {
        wp_safe_redirect(route($name, $key, $query));
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string|null  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return \Illuminate\Contracts\Translation\Translator|string|array|null
     */
    function trans($key = null, $replace = [])
    {
        if (is_null($key)) {
            return app('translator');
        }

        return app('translator')->get($key, $replace, app()->getLocale());
    }
}

if (! function_exists('validator')) {
    /**
     * Create a new Validator instance.
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Contracts\Validation\Factory
     */
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = app(ValidationFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (! function_exists('validate')) {
    /**
     * Validates data
     */
    function validate(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = app(ValidationFactory::class);

        $validator = $factory->make($data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            response([
                'message' => 'validation_error',
            ], 400)->send();

            exit();
        }

        return $data;
    }
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @template TValue
     * @template TArgs
     *
     * @param  TValue|\Closure(TArgs): TValue  $value
     * @param  TArgs  ...$args
     * @return TValue
     */
    function value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
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


if (! function_exists('when')) {
    /**
     * Return a value if the given condition is true.
     *
     * @param  mixed  $condition
     * @param  \Closure|mixed  $value
     * @param  \Closure|mixed  $default
     * @return mixed
     */
    function when($condition, $value, $default = null)
    {
        if ($condition) {
            return value($value, $condition);
        }

        return value($default, $condition);
    }
}
