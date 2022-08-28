<?php

namespace HumbleCore\Menu;

class MenuItem
{
    public $id;

    public $url;

    public $target;

    public $title;

    public $classes;

    public function __construct($item)
    {
        $this->id = $item->ID;
        $this->url = $item->url;
        $this->target = $item->target;
        $this->title = $item->title;
        $this->classes = implode(' ', $item->classes);
    }
}
