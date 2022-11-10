<?php

namespace HumbleCore\ACF;

use HumbleCore\Support\Facades\Action;

class ACFFieldRepository
{
    public array $fieldGroups = [];

    public function registerOptionsPage(array $settings)
    {
        Action::add('acf/init', function () use ($settings) {
            if (! function_exists('acf_add_options_page')) {
                return;
            }

            acf_add_options_page($settings);
        });
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

            $this->registerFieldGroup(new $class);
        });
    }

    public function registerFieldGroup($class): void
    {
        if (! function_exists('register_extended_field_group')) {
            return;
        }

        $this->fieldGroups[] = $class;
    }

    public function initFieldGroups(): void
    {
        foreach ($this->fieldGroups as $fieldGroup) {
            $this->initFieldGroup($fieldGroup);
        }
    }

    public function initFieldGroup($fieldGroup): void
    {
        register_extended_field_group([
            'title' => $fieldGroup::$title,
            'fields' => $fieldGroup::fields(),
            'location' => $fieldGroup::location(),
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'menu_order' => $fieldGroup::$menuOrder ?? 0,
        ]);
    }

    public function registerGoogleMapsKey(string $key): void
    {
        Action::add('acf/init', function () use ($key) {
            acf_update_setting('google_api_key', $key);
        });
    }
}
