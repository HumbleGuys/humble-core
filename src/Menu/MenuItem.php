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

    public $fields;

    public function __construct($item, $childs)
    {
        $this->id = $item->ID;
        $this->url = $item->url;
        $this->target = $item->target;
        $this->title = $item->title;
        $this->classes = implode(' ', $item->classes);
        $this->fields = (object) app('acf')->getFields(null, $item);

        $this->setChilds($childs);

        if ($this->hasActiveChild()) {
            $this->classes .= ' isActive';
        }
    }

    protected function setChilds($childs)
    {
        $this->childs = $childs->filter(function ($child) {
            return $child->menu_item_parent == $this->id;
        })->map(function ($child) use ($childs) {
            return new MenuItem($child, $childs);
        });
    }

    protected function hasActiveChild()
    {
        return $this->childs->first(function ($child) {
            return str_contains($child->classes, 'isActive');
        });
    }
}
