<?php

namespace HumbleCore\ACF;

class ACFFieldRepository
{
    public function registerOptionsPage(array $settings)
    {
        if (! function_exists('acf_add_options_page')) {
            return;
        }

        acf_add_options_page($settings);
    }

    public function loadFieldsFrom(string $path): void
    {
        collect(app('files')->files($path))->each(function ($file) {
            $name = str_replace('.php', '', $file->getFilename());

            $class = str(str_replace(templatePath('app'), '', $file->getPath()))
                ->replace('/', '\\')
                ->prepend('\\App')
                ->append("\\{$name}")
                ->value();

            $this->registerField(new $class);
        });
    }

    public function registerField($class): void
    {
        if (! function_exists('register_extended_field_group')) {
            return;
        }

        register_extended_field_group([
            'title' => $class::$title,
            'fields' => $class::fields(),
            'location' => $class::location(),
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'menu_order' => $class::$menuOrder ?? 0,
        ]);
    }
}
