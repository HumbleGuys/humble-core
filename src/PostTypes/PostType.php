<?php

namespace HumbleCore\PostTypes;

abstract class PostType
{
    public string $name;

    public array $labels = [];

    public string $icon;

    public bool $public = true;

    public bool $has_archive = true;

    public bool $show_ui = true;

    public bool $show_in_menu = true;

    public array $supports = ['title'];

    public $model;

    public bool $sortable = false;
}
