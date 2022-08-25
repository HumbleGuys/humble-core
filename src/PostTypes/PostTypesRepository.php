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
        $this->postTypes[] = $postType->register();
    }
}
