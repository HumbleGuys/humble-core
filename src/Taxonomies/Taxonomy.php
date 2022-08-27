<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Facades\Action;

abstract class Taxonomy
{
    public string $name;

    public array $postTypes = [];

    public array $labels = [
        'name' => '',
        'singular_name' => '',
    ];

    public bool $public = true;

    public ?bool $show_ui = null;

    public ?bool $show_in_menu = null;

    public bool|array $rewrite = true;

    public bool $show_in_rest = false;

    public bool $hierarchical = true;

    public bool $show_in_quick_edit = false;

    public $model;

    public bool $sortable = false;

    public function register(): self
    {
        Action::add('init', function () {
            register_taxonomy($this->name, $this->postTypes, [
                'labels' => $this->labels,
                'public' => $this->public,
                'show_ui' => $this->show_ui,
                'show_in_menu' => $this->show_in_menu,
                'rewrite' => $this->rewrite,
                'show_in_rest' => $this->show_in_rest,
                'hierarchical' => $this->hierarchical,
                'show_in_quick_edit' => $this->show_in_quick_edit,
            ]);
        });

        return $this;
    }
}
