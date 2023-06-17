<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Facades\Filter;
use Illuminate\Support\ServiceProvider;

class TaxonomiesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('taxonomies', function () {
            return new TaxonomiesRepository();
        });

        Filter::add('wp_terms_checklist_args', function ($args) {
            $args['checked_ontop'] = false;

            return $args;
        });
    }
}
