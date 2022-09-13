<?php

namespace HumbleCore\Menu;

class MenuItem
{
    public $id;

    public $url;

    public $target;

    public $title;

    public $classes;

    public $childs;

    public function __construct($item, $childs)
    {
        $this->id = $item->ID;
        $this->url = $item->url;
        $this->target = $item->target;
        $this->title = $item->title;
        $this->classes = implode(' ', $item->classes);

        $this->setChilds($childs);
    }

    protected function setChilds($childs)
    {
        $this->childs = $childs->filter(function ($child) {
            return $child->menu_item_parent == $this->id;
        })->map(function ($child) use ($childs) {
            return new MenuItem($child, $childs);
        });
    }
}
