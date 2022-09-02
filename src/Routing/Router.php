<?php

namespace HumbleCore\Routing;

use UnexpectedValueException;

class Router
{
    protected array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function wp(string $path, $handler): void
    {
        $this->addRoute('WP', $path, $handler);
    }

    public function addRoute($verb, $path, $handler)
    {
        $this->routes[] = new Route($verb, $path, $handler);
    }

    public function getRoutes(): array
    {
        return $this->routes;
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
