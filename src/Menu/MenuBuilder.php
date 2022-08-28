<?php

namespace HumbleCore\Menu;

class MenuBuilder
{
    public function __construct(public string $name)
    {
    }

    public function get()
    {
        $locations = get_nav_menu_locations();

        if (empty($locations[$this->name])) {
            return collect();
        }

        return collect(wp_get_nav_menu_items($locations[$this->name]))->map(function ($item) {
            return new MenuItem($item);
        });
    }
}
