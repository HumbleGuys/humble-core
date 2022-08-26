<?php

namespace HumbleCore\Hook;

use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('action', function () {
            return new ActionBuilder;
        });

        $this->app->bind('filter', function () {
            return new FilterBuilder;
        });
    }
}
