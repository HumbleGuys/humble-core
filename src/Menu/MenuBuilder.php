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

        $menuItems = collect(wp_get_nav_menu_items($locations[$this->name]));

        $activeChecker = new ActiveChecker($menuItems);

        $items = $menuItems->each(function ($menuItem) use ($activeChecker) {
            if ($activeChecker->isActive($menuItem)) {
                $menuItem->classes[] = 'isActive';
            }
        });

        $childs = $items->filter(function ($item) {
            return $item->menu_item_parent != 0;
        });

        return $items
            ->filter(function ($item) {
                return $item->menu_item_parent == 0;
            })
            ->map(function ($item) use ($childs) {
                return new MenuItem($item, $childs);
            });
    }
}
