<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Facades\Blade;

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

    public bool $hideDescriptionField = true;

    public bool $hideCreateOnPosts = true;

    public bool $show_in_quick_edit = false;

    public bool $show_admin_column = true;

    public $model;

    public bool $sortable = false;

    public function register(): self
    {
        Action::add('init', function () {
            if (method_exists($this, 'beforeRegister')) {
                $this->beforeRegister();
            }

            register_taxonomy($this->name, $this->postTypes, [
                'labels' => $this->labels,
                'public' => $this->public,
                'show_ui' => $this->show_ui,
                'show_in_menu' => $this->show_in_menu,
                'rewrite' => $this->rewrite,
                'show_in_rest' => $this->show_in_rest,
                'hierarchical' => $this->hierarchical,
                'show_in_quick_edit' => $this->show_in_quick_edit,
                'show_admin_column' => $this->show_admin_column,
            ]);
        });

        if ($this->hideCreateOnPosts) {
            $this->hideCreateOnPosts();
        }

        if ($this->hideDescriptionField) {
            $this->hideDescriptionField();
        }

        if ($this->sortable) {
            new TermSorter($this);
        }

        return $this;
    }

    protected function hideCreateOnPosts()
    {
        Action::add('admin_footer', function () {
            $html =
            <<<'blade'
                <style>
                    #{{ $name }}-adder {
                        display: none;
                    }
                </style>
            blade;

            echo Blade::render($html, [
                'name' => $this->name,
            ]);
        });
    }

    protected function hideDescriptionField()
    {
        $callback = function () {
            $css =
                <<<'blade'
                    <style>
                        .term-description-wrap {
                            display: none;
                        }
                    </style>
                blade;

            echo Blade::render($css);
        };

        Action::add("{$this->name}_add_form", $callback);
        Action::add("{$this->name}_edit_form", $callback);

        Action::add("manage_edit-{$this->name}_columns", function ($columns) {
            if (isset($columns['description'])) {
                unset($columns['description']);
            }

            return $columns;
        });
    }
}
