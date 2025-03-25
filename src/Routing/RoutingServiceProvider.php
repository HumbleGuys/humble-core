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

            try {
                echo $this->app->router->initWp($template);
            }catch (\Exception $e){
                logger()->error($e->getMessage());

                if (!empty($this->app->router->serverErrorHandler)) {
                    call_user_func($this->app->router->serverErrorHandler, $e);
                }

                response("500 error", 500, [
                    'Cache-Control' => 'no-cache',
                ])->send();

                exit();
            }
        });
    }

    public function boot()
    {
        app('router')->resolveRoute();
    }
}
