<?php

namespace HumbleCore\PostTypes;

use Illuminate\Support\ServiceProvider;

class PostTypesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('postTypes', function () {
            return new PostTypesRepository();
        });
    }
}
