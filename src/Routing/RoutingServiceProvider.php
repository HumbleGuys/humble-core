<?php

namespace HumbleCore\Routing;

use HumbleCore\Support\Facades\Filter;
use Illuminate\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('router', function () {
            return new Router();
        });

        Filter::add('template_include', function ($template) {
            echo $this->app->router->initWp($template);
        });
    }

    public function boot()
    {
        app('router')->resolveApiRoute();
    }
}
