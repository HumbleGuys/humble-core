<?php

namespace HumbleCore\Menu;

class MenuRepository
{
    public array $menus = [];

    public function register($location, $name)
    {
        register_nav_menu($location, $name);

        $this->menus[$location] = $name;

        return $this;
    }
}
