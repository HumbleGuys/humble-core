<?php

namespace HumbleCore\Menu;

class MenuRepository
{
    public array $menus = [];

    public function register(string $location, string $name): self
    {
        register_nav_menu($location, $name);

        $this->menus[$location] = $name;

        return $this;
    }

    public function get(string $name)
    {
        return (new MenuBuilder($name))->get();
    }
}
