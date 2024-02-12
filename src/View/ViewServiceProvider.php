<?php

namespace HumbleCore\View;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\View\ViewServiceProvider as IlluminateViewServiceProvider;

class ViewServiceProvider extends IlluminateViewServiceProvider
{
    protected array $viewComponents = [];

    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });

        $this->loadViewComponentsFrom(templatePath('app/Views'));

        if (! empty($this->viewComponents)) {
            $this->registerViewComponentsRoutes();
        }
    }

    public function boot()
    {
        Blade::directive('svg', function ($expression) {
            return "<?php echo svg($expression) ?>";
        });

        if (! empty($this->viewComponents)) {
            $this->registerViewComponents();
        }
    }

    protected function loadViewComponentsFrom(string $path): void
    {
        if (! File::exists($path)) {
            return;
        }

        collect(app('files')->files($path))->each(function ($file) {
            $name = str_replace('.php', '', $file->getFilename());

            $class = str(str_replace(templatePath('app'), '', $file->getPath()))
                ->replace('/', '\\')
                ->prepend('\\App')
                ->append("\\{$name}")
                ->value();

            $this->viewComponents[] = $class;
        });
    }

    protected function registerViewComponentsRoutes(): void
    {
        foreach ($this->viewComponents as $viewComponent) {
            if (method_exists($viewComponent, 'asController')) {
                $componentPath = $viewComponent::$componentPath;

                $componentUrlPath = str($componentPath)->replace('.', '/');

                app('router')->addRoute('GET', "/api/views/{$componentUrlPath}", [$viewComponent, 'asController'], "views.{$componentPath}");
            }
        }
    }

    protected function registerViewComponents(): void
    {
        foreach ($this->viewComponents as $viewComponent) {
            $componentPath = $viewComponent::$componentPath;

            Blade::component($componentPath, $viewComponent);
        }
    }
}
