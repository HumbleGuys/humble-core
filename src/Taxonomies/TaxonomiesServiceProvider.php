<?php

namespace HumbleCore\Taxonomies;

use Illuminate\Support\ServiceProvider;

class TaxonomiesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('taxonomies', function () {
            return new TaxonomiesRepository();
        });
    }
}
