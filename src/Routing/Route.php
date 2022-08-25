<?php

namespace HumbleCore\Routing;

use Illuminate\Support\Str;

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
        }

        return false;
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

    public function resolve()
    {
        if (is_callable($this->handler)) {
            echo call_user_func($this->handler);
        }

        if (is_array($this->handler)) {
            [$class, $method] = $this->handler;

            echo (new $class)->{$method}();
        }
    }
}