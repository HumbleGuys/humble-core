<?php

namespace HumbleCore\Routing;

use HumbleCore\Support\Facades\Action;
use UnexpectedValueException;

class Router
{
    protected array $routes = [];

    protected string $pathPrefix = '';

    protected ?Route $currentRoute = null;

    public $serverErrorHandler;

    public $underConstructionHandler;

    public function get(string $path, $handler): Route
    {
        return $this->addRoute('GET', $this->addPathPrefix($path), $handler);
    }

    public function post(string $path, $handler): Route
    {
        return $this->addRoute('POST', $this->addPathPrefix($path), $handler);
    }

    public function put(string $path, $handler): Route
    {
        return $this->addRoute('PUT', $this->addPathPrefix($path), $handler);
    }

    public function delete(string $path, $handler): Route
    {
        return $this->addRoute('DELETE', $this->addPathPrefix($path), $handler);
    }

    public function wp(string $path, $handler): Route
    {
        return $this->addRoute('WP', $path, $handler);
    }

    public function addRoute($verb, $path, $handler, $name = null)
    {
        $route = new Route($verb, $path, $handler, $name);

        $this->routes[] = $route;

        return $route;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoute(string $name): Route
    {
        return collect($this->routes)->firstWhere('name', $name);
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    public function loadRoutesFrom($path)
    {
        include $path;
    }

    public function loadApiRoutesFrom($path)
    {
        $this->pathPrefix = '/api';

        include $path;

        $this->pathPrefix = '';
    }

    public function resolveRoute()
    {
        $route = collect($this->routes)->filter(function ($route) {
            return $route->verb !== 'WP';
        })->first(function ($route) {
            return $route->isMatching();
        });

        if ($route) {
            $this->currentRoute = $route;

            $arguments = [];

            if (str_contains($route->path, '{')) {
                $requestParts = str(request()->server('REQUEST_URI'))->beforeLast('?')->explode('/');

                $arguments = str($route->path)->explode('/')->map(function ($part, $index) use ($requestParts) {
                    if (! str($part)->startsWith('{')) {
                        return;
                    }

                    return $requestParts[$index];
                })->filter()->values();
            }

            Action::add('wp_loaded', function () use ($route, $arguments) {
                $res = $route->resolve($arguments ?? []);
                response($res, 200, [
                    'Cache-Control' => 'public',
                ])->send();
                exit();
            });
        }
    }

    public function initWp($template)
    {
        $route = collect($this->routes)->filter(function ($route) {
            return $route->verb === 'WP';
        })->first(function ($route) {
            return $route->isMatching();
        });

        if ($route) {
            $this->currentRoute = $route;

            return $route->resolveWpRoute();
        }

        throw new UnexpectedValueException('No route found.');

        return $template;
    }

    public function setServerErrorHandler(callable $handler)
    {
        $this->serverErrorHandler = $handler;
    }

    public function setUnderConstructionHandler(callable $handler)
    {
        $this->underConstructionHandler = $handler;
    }

    protected function addPathPrefix(string $path): string
    {
        return "{$this->pathPrefix}/{$path}";
    }
}
