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
            if (app()->isProduction() && app()->isUnderConstruction() && ! is_user_logged_in()) {
                echo get_bloginfo('name');

                return;
            }

            echo $this->app->router->initWp($template);
        });
    }

    public function boot()
    {
        app('router')->resolveRoute();
    }
}
