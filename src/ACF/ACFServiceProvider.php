<?php

namespace HumbleCore\ACF;

use HumbleCore\Support\Facades\Action;
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

        Action::add('acf/init', function () {
            app('acf.fields')->initFieldGroups();
        });
    }

    public function boot()
    {

    }
}
