<?php

namespace HumbleCore\Routing;

use Illuminate\Support\Str;
use UnexpectedValueException;

class Route
{
    public function __construct(
        public $verb,
        public $path,
        public $handler
    ) {
    }

    public function isMatching()
    {
        if ($this->verb === 'WP') {
            return $this->isMatchingWpRoute();
        } else {
            return $this->isMatchingApiRoute();
        }

        return false;
    }

    public function isMatchingApiRoute()
    {
        $route = request()->server('REQUEST_URI');

        if (! Str::startsWith($route, '/api')) {
            return false;
        }

        if (request()->server('REQUEST_METHOD') !== $this->verb) {
            return false;
        }

        return str($route)->replace('/api', '')->replaceFirst('/', '')->replaceLast('/', '')->is($this->path);
    }

    public function isMatchingWpRoute()
    {
        if (Str::startsWith($this->path, 'template-') && is_page_template(Str::after($this->path, 'template-'))) {
            return true;
        }

        if (is_home() && ! is_front_page() && $this->path === 'archive-post') {
            return true;
        }

        if (is_singular() && Str::startsWith($this->path, 'single')) {
            return is_singular(Str::after($this->path, 'single-'));
        }

        if (is_post_type_archive() && Str::startsWith($this->path, 'archive')) {
            return is_post_type_archive(Str::after($this->path, 'archive-'));
        }

        if (is_tax() && Str::startsWith($this->path, 'taxonomy')) {
            return is_tax(Str::after($this->path, 'taxonomy-'));
        }

        $routeChecks = [
            '404' => fn () => is_404(),
            'search' => fn () => is_search(),
            'front-page' => fn () => is_front_page(),
            'page' => fn () => is_page() && ! is_page_template(),
        ];

        return isset($routeChecks[$this->path]) && $routeChecks[$this->path]();
    }

    public function getWpIdForRoute()
    {
        if (Str::startsWith($this->path, 'template-') && is_page_template(Str::after($this->path, 'template-'))) {
            return get_the_ID();
        }

        if (is_home() && ! is_front_page() && $this->path === 'archive-post') {
            return get_option('page_for_posts');
        }

        if (is_singular() && Str::startsWith($this->path, 'single')) {
            return get_the_ID();
        }

        if (is_404() || is_search()) {
            return;
        }

        if (is_front_page() || is_page()) {
            return get_the_ID();
        }
    }

    public function resolveWpRoute()
    {
        $id = $this->getWpIdForRoute();

        return $this->resolve($id);
    }

    public function resolve($arg = null)
    {
        if (is_callable($this->handler)) {
            return call_user_func($this->handler, $arg);
        }

        if (is_array($this->handler)) {
            [$class, $method] = $this->handler;

            return (new $class)->{$method}($arg);
        }

        if (is_string($this->handler) && method_exists($this->handler, '__invoke')) {
            return (new $this->handler)->__invoke($arg);
        }

        throw new UnexpectedValueException("Invalid route action for: [{$this->path}].");
    }
}
