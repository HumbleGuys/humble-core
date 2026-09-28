<?php

namespace HumbleCore\Routing;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Collection;

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

    public function resolveRoute(): void
    {
        $routes = collect($this->routes)->filter(fn (Route $r) => $r->verb !== 'WP');

        $route = $routes->first(fn (Route $r) => $r->isMatching());

        if ($route) {
            $this->currentRoute = $route;

            $arguments = $route->parameters();

            Action::add('wp_loaded', function () use ($route, $arguments) {
                $result = $route->resolve($arguments);
                app(ApiRouteResultHandler::class)->toResponse($result)->send();
                exit();
            });

            return;
        }

        $matchingRoutes = $routes->filter(fn (Route $r) => $r->isMatchingPath());

        if (
            request()->server('REQUEST_METHOD') === 'OPTIONS' &&
            $matchingRoutes->isNotEmpty()
        ) {
            $this->sendCorsPreflightResponse($matchingRoutes);

            return;
        }
    }

    /** @param Collection<int, Route> $matchingRoutes */
    private function sendCorsPreflightResponse(Collection $matchingRoutes): void
    {
        $allowedMethods = $matchingRoutes
            ->pluck('verb')
            ->push('OPTIONS')
            ->unique()
            ->join(', ');

        $requestedHeaders = trim(request()->server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') ?? '') ?: 'Content-Type';

        response(status: 204, headers: [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => $allowedMethods,
            'Access-Control-Allow-Headers' => $requestedHeaders,
            'Access-Control-Max-Age' => '86400',
        ])->send();

        exit();
    }

    public function initWp($template): mixed
    {
        $routes = collect($this->routes)->filter(fn (Route $route) => $route->verb === 'WP');
        $route = $routes->first(fn (Route $route) => $route->isMatching());

        if (! $route) {
            global $wp_query;

            $wp_query->set_404();
            status_header(404);
            nocache_headers();

            $route = $routes->firstWhere('path', '404');
        }

        $this->currentRoute = $route;

        if ($route) {
            return $route->resolveWpRoute();
        }

        return response('Not Found', 404, ['Cache-Control' => 'no-cache']);
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
