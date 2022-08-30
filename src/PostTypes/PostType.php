<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Facades\Action;

abstract class PostType
{
    public string $name;

    public array $labels = [
        'name' => '',
        'singular_name' => '',
    ];

    public string $icon;

    public bool $public = true;

    public ?bool $has_archive = null;

    public ?bool $show_ui = null;

    public ?bool $show_in_menu = null;

    public array $supports = ['title'];

    public ?bool $menu_position = null;

    public bool|array $rewrite = true;

    public bool $show_in_rest = false;

    public bool $hierarchical = false;

    public $model;

    public bool $sortable = false;

    public function register(): self
    {
        Action::add('init', function () {
            register_post_type($this->name, [
                'labels' => $this->labels,
                'menu_icon' => $this->icon,
                'public' => $this->public,
                'has_archive' => $this->has_archive,
                'show_ui' => $this->show_ui,
                'show_in_menu' => $this->show_in_menu,
                'supports' => $this->supports,
                'menu_position' => $this->menu_position,
                'rewrite' => $this->rewrite,
                'show_in_rest' => $this->show_in_rest,
                'hierarchical' => $this->hierarchical,
            ]);
        });

        if ($this->sortable) {
            new PostSorter($this);
        }

        return $this;
    }
}
