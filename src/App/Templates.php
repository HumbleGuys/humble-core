<?php

namespace HumbleCore\App;

use HumbleCore\Support\Facades\Filter;

class Templates
{
    protected array $templates = [];

    public function add(string $key, string $name): self
    {
        $this->templates[$key] = $name;

        return $this;
    }

    public function remove(string $key): self
    {
        unset($this->templates[$key]);

        return $this;
    }

    public function register(): void
    {
        Filter::add('theme_page_templates', function () {
            return $this->templates;
        });
    }
}
