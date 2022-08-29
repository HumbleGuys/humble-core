<?php

namespace HumbleCore\ACF;

use HumbleCore\Support\Facades\Action;
use HumbleCore\Support\Facades\Filter;
use Illuminate\Support\ServiceProvider;

class ACFServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('acf', function () {
            return new ACF;
        });

        $this->app->singleton('acf.fields', function () {
            return new ACFFieldRepository;
        });

        Action::add('acf/init', function () {
            app('acf.fields')->initFieldGroups();
        });

        Filter::add('acf/format_value/type=date_picker', [ACFTransformer::class, 'date'], 100, 1);
        Filter::add('acf/format_value/type=file', [ACFTransformer::class, 'file'], 100, 1);
        Filter::add('acf/format_value/type=flexible_content', [ACFTransformer::class, 'flexibleContent'], 100, 1);
        Filter::add('acf/format_value/type=googe_map', [ACFTransformer::class, 'googleMap'], 100, 1);
        Filter::add('acf/format_value/type=group', [ACFTransformer::class, 'group'], 100, 1);
        Filter::add('acf/format_value/type=image', [ACFTransformer::class, 'image'], 100, 1);
        Filter::add('acf/format_value/type=link', [ACFTransformer::class, 'link'], 100, 1);
        Filter::add('acf/format_value/type=repeater', [ACFTransformer::class, 'repeater'], 100, 1);
    }

    public function boot()
    {
    }
}
