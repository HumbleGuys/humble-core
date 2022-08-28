<?php

namespace HumbleCore\Routing;

use Illuminate\Support\Str;

class Api
{
    public function setBaseApiUrl()
    {
        $this->addRewrite('/api/{id}');
    }

    public function addRewrite($path)
    {
        $name = Str::slug($path, '_');

        if (str_contains($path, '{id}')) {
            $path = Str::replace('{id}', '([^/]+)', $path);

            add_action('init', function () use ($name, $path) {
                add_rewrite_rule($path, 'index.php?'.$name.'=$matches[1]', 'top');
            });

            add_filter('query_vars', function ($vars) use ($name) {
                $vars[] = $name;

                return $vars;
            });

            add_action('template_redirect', function () use ($name) {
                if (get_query_var($name)) {
                    app('router')->resolveApiRoute(get_query_var($name));
                }
            });
        } else {
            $path = "^{$path}/?$";

            add_action('init', function () use ($name, $path) {
                add_rewrite_rule($path, 'index.php?'.$name.'=true', 'top');
            });

            add_filter('query_vars', function ($vars) use ($name) {
                $vars[] = $name;

                return $vars;
            });

            add_action('template_redirect', function () use ($name) {
                if (get_query_var($name)) {
                    app('router')->resolveApiRoute(get_query_var($name));
                }
            });
        }
    }
}
