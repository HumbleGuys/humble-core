<?php

namespace HumbleCore\Routing;

use UnexpectedValueException;

class Router
{
    protected array $routes = [];

    public function get(string $path, $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
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

    public function loadRoutesFrom($path)
    {
        include $path;
    }

    public function loadApiRoutesFrom($path)
    {
        include $path;
    }

    public function resolveApiRoute()
    {
        $route = collect($this->routes)->filter(function ($route) {
            return $route->verb !== 'WP';
        })->first(function ($route) {
            return $route->isMatching();
        });

        if ($route) {
            $res = $route->resolve();

            response($res, 200)->send();
            exit();
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
            return $route->resolveWpRoute();
        }

        throw new UnexpectedValueException('No route found.');

        return $template;
    }
}
