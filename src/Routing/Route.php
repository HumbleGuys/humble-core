<?php

namespace HumbleCore\Routing;

use Illuminate\Support\Str;
use UnexpectedValueException;

class Route
{
    public function __construct(
        public $verb,
        public $path,
        public $handler,
        public $name = null
    ) {}

    public function isMatching()
    {
        if ($this->verb === 'WP') {
            return $this->isMatchingWpRoute();
        } else {
            return $this->isMatchingRoute();
        }

        return false;
    }

    public function isMatchingRoute()
    {
        $route = str(request()->server('REQUEST_URI'))
            ->beforeLast('?')
            ->rtrim('/');

        if (request()->server('REQUEST_METHOD') !== $this->verb) {
            return false;
        }

        $routePath = str($this->path)->explode('/')->map(function ($part) {
            return str($part)->startsWith('{') ? '*' : $part;
        })->join('/');

        return $route->is($routePath) && $route->substrCount('/') === str($routePath)->substrCount('/');
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

        if (is_post_type_archive() && Str::startsWith($this->path, 'archive')) {
            return app('postTypes')->getArchiveIdFromPostType(Str::after($this->path, 'archive-'));
        }

        if (is_tax() && Str::startsWith($this->path, 'taxonomy')) {
            return get_queried_object_id();
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

        return $this->resolve([$id]);
    }

    public function resolve($arg = [])
    {
        if (is_callable($this->handler)) {
            return call_user_func($this->handler, ...$arg);
        }

        if (is_array($this->handler)) {
            [$class, $method] = $this->handler;

            return (new $class)->{$method}(...$arg);
        }

        if (is_string($this->handler) && method_exists($this->handler, '__invoke')) {
            return (new $this->handler)->__invoke(...$arg);
        }

        throw new UnexpectedValueException("Invalid route action for: [{$this->path}].");
    }

    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function url($key)
    {
        if ($this->verb !== 'WP') {
            $baseUrl = get_home_url();

            return "{$baseUrl}{$this->path}/";
        }

        if ($this->path === 'front-page') {
            return get_the_permalink(get_option('page_on_front'));
        }

        if ($this->path === 'page' || Str::startsWith($this->path, 'template-')) {
            throw_if(empty($key), 'Key paramenter is missing');

            return get_the_permalink($key);
        }

        if (Str::startsWith($this->path, 'single')) {
            throw_if(empty($key), 'Key paramenter is missing');

            return get_the_permalink($key);
        }

        if ($this->path === 'archive-post') {
            return get_the_permalink(get_option('page_for_posts'));
        }

        if (Str::startsWith($this->path, 'archive')) {
            $id = app('postTypes')->getArchiveIdFromPostType(Str::after($this->path, 'archive-'));

            return get_the_permalink($id);
        }

        if (Str::startsWith($this->path, 'taxonomy')) {
            throw_if(empty($key), 'Key paramenter is missing');

            return get_term_link($key, Str::after($this->path, 'taxonomy-'));
        }

        throw ('Unkown route type');
    }
}
