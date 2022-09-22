<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Facades\ACF;

class TermBuilder
{
    protected $findId;

    protected $name;

    protected $postId;

    protected $orderBy;

    protected $order = 'asc';

    protected $acf;

    protected $hideEmpty = true;

    protected $includeChilds = false;

    protected $permalink;

    protected $search;

    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function find(int $id): TermModel
    {
        $this->findId = $id;

        return $this->model;
    }

    public function when($value, $callback)
    {
        if ($value) {
            return $callback($this->model);
        }

        return $this->model;
    }

    public function name(string $name): TermModel
    {
        $this->name = $name;

        return $this->model;
    }

    public function search(string $query): TermModel
    {
        $this->search = urldecode($query);

        return $this->model;
    }

    public function forPost(int $postId): TermModel
    {
        $this->postId = $postId;

        return $this->model;
    }

    public function order(string $order): TermModel
    {
        $this->order = $order;

        return $this->model;
    }

    public function orderBy(string $orderBy, ?string $order = 'asc'): TermModel
    {
        $this->orderBy = $orderBy;
        $this->order = $order;

        return $this->model;
    }

    public function orderBySortOrder(string $order = 'asc'): TermModel
    {
        $this->orderBy = 'sortorder';
        $this->order = $order;

        return $this->model;
    }

    public function orderByTitle(string $order = 'asc'): TermModel
    {
        $this->orderBy = 'title';
        $this->order = $order;

        return $this->model;
    }

    public function withAcf($fields = true): TermModel
    {
        $this->acf = $fields;

        return $this->model;
    }

    public function withEmpty(): TermModel
    {
        $this->hideEmpty = false;

        return $this->model;
    }

    public function withPermalink(): TermModel
    {
        $this->permalink = true;

        return $this->model;
    }

    public function withChilds(): TermModel
    {
        $this->includeChilds = true;

        return $this->model;
    }

    public function getItems()
    {
        if ($this->findId) {
            return [$this->setupTerm(get_term($this->findId))];
        }

        $terms = $this->getTerms();

        if (! $terms) {
            return [];
        }

        $terms = array_map(function ($term) {
            return $this->setupTerm($term);
        }, $terms);

        if ($this->orderBy) {
            $terms = $this->sortTerms($terms);
        }

        if (! $this->includeChilds) {
            return $terms;
        }

        $out = [];

        foreach ($terms as $key => $term) {
            if ($term->parent === 0) {
                $term->childs = $this->findChilds($terms, $term->id);
                $out[] = $term;
            }
        }

        return $out;
    }

    public function get()
    {
        return $this->model::hydrate($this->getItems(), $this->model->getAppends(), $this->model->getMutators());
    }

    public function first()
    {
        return $this->get()->first();
    }

    protected function findChilds(array $terms, int $termId): array
    {
        $childs = [];

        foreach ($terms as $key => $term) {
            if ($term->parent === $termId) {
                $term->childs = $this->findChilds($terms, $term->id);
                $childs[] = $term;
            }
        }

        return $childs;
    }

    protected function getTerms(): ?array
    {
        if ($this->postId) {
            $terms = get_the_terms($this->postId, $this->name);

            return is_array($terms) ? $terms : null;
        }

        return get_terms([
            'taxonomy' => $this->name,
            'hide_empty' => $this->hideEmpty,
            'search' => $this->search,
        ]);
    }

    protected function setupTerm(object $term): object
    {
        $item = [
            'id' => $term->term_id,
            'title' => html_entity_decode($term->name, ENT_QUOTES),
            'slug' => $term->slug,
            'taxonomy' => $term->taxonomy,
            'count' => $term->count,
            'parent' => $term->parent,
        ];

        if ($this->acf) {
            $item = array_merge($item, ACF::getFields($this->acf, $term));
        }

        if ($this->permalink) {
            $item['permalink'] = $this->getPermalink($term->term_id);
        }

        return (object) $item;
    }

    protected function sortTerms(array $terms): array
    {
        if ($this->orderBy === 'sortorder') {
            return $this->sortTermsBySortOrder($terms);
        }

        if ($this->orderBy === 'title') {
            return $this->sortTermsByTitle($terms);
        }

        return $terms;
    }

    protected function sortTermsBySortOrder(array $terms): array
    {
        $terms = array_map(function ($term) {
            $term->sortorder = get_option($this->name.'_'.$term->id.'_sortorder', 99999999);

            return $term;
        }, $terms);

        usort($terms, function ($a, $b) {
            if ($this->order === 'desc') {
                return $b->sortorder - $a->sortorder;
            }

            return $a->sortorder - $b->sortorder;
        });

        return $terms;
    }

    protected function sortTermsByTitle(array $terms): array
    {
        usort($terms, function ($a, $b) {
            if ($this->order === 'desc') {
                return strcasecmp($b->title, $a->title);
            }

            return strcasecmp($a->title, $b->title);
        });

        return $terms;
    }

    protected function getPermalink(int $id): string
    {
        return get_term_link($id);
    }
}
