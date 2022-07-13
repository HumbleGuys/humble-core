<?php

namespace HumbleCore\View;

use Illuminate\View\ViewServiceProvider as IlluminateViewServiceProvider;

class ViewServiceProvider extends IlluminateViewServiceProvider
{
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });
    }
}
