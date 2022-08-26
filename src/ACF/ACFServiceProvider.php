<?php

namespace HumbleCore\ACF;

use Illuminate\Support\ServiceProvider;

class ACFServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('acf', function () {
        });

        $this->app->singleton('acf.fields', function () {
            return new ACFFieldRepository;
        });
    }

    public function boot()
    {
        app('acf.fields')->initFieldGroups();
    }
}
