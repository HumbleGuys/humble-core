<?php

namespace HumbleCore\Routing;

use Illuminate\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('router', function () {
            return new Router();
        });
    }

    public function boot()
    {
        add_filter('template_include', function ($template) {
            return $this->app->router->initWp($template);
        });
    }
}
