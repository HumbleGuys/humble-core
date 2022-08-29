<?php

namespace HumbleCore\View;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewServiceProvider as IlluminateViewServiceProvider;

class ViewServiceProvider extends IlluminateViewServiceProvider
{
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });
    }

    public function boot()
    {
        Blade::directive('svg', function ($expression) {
            return "<?php echo svg($expression) ?>";
        });
    }
}
