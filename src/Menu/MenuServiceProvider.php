<?php

namespace HumbleCore\Menu;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('menu', function () {
            return new MenuRepository();
        });
    }
}
