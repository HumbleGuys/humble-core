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

        $this->app->singleton(WordPressRouteResultHandler::class);

        Filter::add('template_include', function ($template) {
            try {
                if (app()->isProduction() && app()->isUnderConstruction() && ! is_user_logged_in()) {
                    if (! empty($this->app->router->underConstructionHandler)) {
                        $this->app->make(WordPressRouteResultHandler::class)->send(
                            call_user_func($this->app->router->underConstructionHandler)
                        );

                        return;
                    }

                    echo get_bloginfo('name');

                    return;
                }

                $this->app->make(WordPressRouteResultHandler::class)->send(
                    $this->app->router->initWp($template)
                );
            } catch (\Throwable $e) {
                if (app()->isLocal()) {
                    throw $e;
                }

                logger()->error($e->getMessage());

                if (! empty($this->app->router->serverErrorHandler)) {
                    call_user_func($this->app->router->serverErrorHandler, $e);
                }

                response('500 error', 500, [
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
