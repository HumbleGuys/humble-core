<?php

namespace HumbleCore\PostTypes;

class PostTypesRepository
{
    protected array $postTypes = [];

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
        register_post_type($postType->name, [
            'name' => $postType->name,
            'labels' => $postType->labels,
            'menu_icon' => $postType->icon,
            'public' => $postType->public,
            'has_archive' => $postType->has_archive,
            'show_ui' => $postType->show_ui,
            'show_in_menu' => $postType->show_in_menu,
            'supports' => $postType->supports,
        ]);

        $this->postTypes[] = $postType;
    }
}
