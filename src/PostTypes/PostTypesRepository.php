<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Collection;

class PostTypesRepository
{
    protected array $postTypes = [];

    public function loadMenuIcons()
    {
        Action::add('admin_enqueue_scripts', function () {
            wp_enqueue_style('line-awesome', 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css', false, null);
        });
    }

    public function loadPostTypesFrom(string $path): void
    {
        collect(app('files')->files($path))->each(function ($file) {
            $name = str_replace('.php', '', $file->getFilename());

            $class = str(str_replace(templatePath('app'), '', $file->getPath()))
                ->replace('/', '\\')
                ->prepend('\\App')
                ->append("\\{$name}")
                ->value();

            $this->register(new $class);
        });
    }

    public function register(PostType $postType): void
    {
        $this->postTypes[] = $postType->register();
    }

    public function getPostType(string $name): ?PostType
    {
        return $this->getPostTypes()->firstWhere('name', $name);
    }

    public function getPostTypes(): Collection
    {
        return collect($this->postTypes);
    }
}
