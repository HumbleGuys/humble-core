<?php

namespace HumbleCore\PostTypes;

use Carbon\Carbon;
use HumbleCore\Support\Facades\ACF;
use HumbleCore\Taxonomies\TermModel;
use Illuminate\Support\Collection;

class PostBuilder
{
    public $findId;

    public $take = -1;

    public $excludeIds;

    public $title;

    public $permalink;

    public $withDate;

    public $acf;

    public $offset = 0;

    public $orderBy = 'post_date';

    public $order = 'desc';

    public $search;

    public $postStatus = 'publish';

    public $postName;

    private $metaQuery = [
        'relation' => 'AND',
        'metaFilters' => [],
    ];

    public $taxQuery;

    public $postType;

    public $post;

    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function postType(string $postType): PostModel
    {
        $this->postType = $postType;

        return $this->model;
    }

    public function when($value, $callback)
    {
        if ($value) {
            return $callback($this->model);
        }

        return $this->model;
    }

    public function name(string $name): PostModel
    {
        $this->postName = $name;

        return $this->model;
    }

    public function status($status): PostModel
    {
        $this->postStatus = $status;

        return $this->model;
    }

    public function take(int $take): PostModel
    {
        $this->take = $take;

        return $this->model;
    }

    public function find($ids): PostModel
    {
        $this->findId = ! empty($ids) ? $ids : -1;

        if (! is_array($ids)) {
            $this->findId = [$this->findId];
        }

        return $this->model;
    }

    public function search(string $query): PostModel
    {
        $this->search = urldecode($query);

        return $this->model;
    }

    public function where($field, $operator = null, $value = null, $type = null, $relation = null): PostModel
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2
        );

        if (empty($this->metaQuery['metaFilters']['relation'])) {
            $this->metaQuery['metaFilters']['relation'] = 'AND';
        }

        if ($relation) {
            $this->metaQuery['metaFilters']['relation'] = $relation;
        }

        $this->metaQuery['metaFilters'][] = [
            'key' => $field,
            'value' => $value,
            'type' => $type,
            'compare' => $operator,
        ];

        return $this->model;
    }

    public function whereDate($field, $operator, $value, $relation = null): PostModel
    {
        return $this->where($field, $operator, $value, 'DATE', $relation);
    }

    public function whereHasTerm(TermModel $term): PostModel
    {
        $this->taxQuery = [
            [
                [
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->id,
                ],
            ],
        ];

        return $this->model;
    }

    public function whereInTerms(Collection $terms, string $relation = 'OR'): PostModel
    {
        $taxQuery = $terms->map(function ($term) {
            return [
                'taxonomy' => $term->taxonomy,
                'field' => 'term_id',
                'terms' => $term->id,
            ];
        })->all();

        $this->taxQuery = [
            [
                'relation' => $relation,
                ...$taxQuery,
            ],
        ];

        return $this->model;
    }

    public function orderByTitle(string $order = 'asc'): PostModel
    {
        $this->orderBy = 'menu_order';
        $this->order = $order;

        return $this->model;
    }

    public function orderBySortOrder(string $order = 'asc'): PostModel
    {
        $this->orderBy = 'menu_order';
        $this->order = $order;

        return $this->model;
    }

    public function orderByRandom(): PostModel
    {
        $this->orderBy = 'rand';
        $this->order = 'asc';

        return $this->model;
    }

    public function exclude($ids): PostModel
    {
        $this->excludeIds = is_array($ids) ? $ids : [$ids];

        return $this->model;
    }

    public function withDate(): PostModel
    {
        $this->withDate = true;

        return $this->model;
    }

    public function withTitle(): PostModel
    {
        $this->title = true;

        return $this->model;
    }

    public function withPermalink(): PostModel
    {
        $this->permalink = true;

        return $this->model;
    }

    public function offset(int $offset): PostModel
    {
        $this->offset = $offset;

        return $this->model;
    }

    public function withAcf($fields = true): PostModel
    {
        $this->acf = $fields;

        return $this->model;
    }

    public function paginate(int $perPage = 20): array
    {
        $currentPage = (int) request('page') ?: 1;

        $offset = ($currentPage - 1) * $perPage;

        $this->take(-1);
        $this->offset(0);

        $count = count($this->getPosts($this->findId));

        $lastPage = (int) ceil($count / $perPage);

        $this->take($perPage);
        $this->offset($offset);

        $items = $this->get();

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'data' => $items,
            'total' => $count,
        ];
    }

    public function get()
    {
        return $this->model::hydrate($this->getItems(), $this->model->getAppends());
    }

    public function first()
    {
        $this->take = 1;

        return $this->get()->first();
    }

    protected function getItems()
    {
        if ($this->findId) {
            $posts = $this->getPosts($this->findId);
        } else {
            $posts = $this->getPosts();
        }

        if (isset($posts)) {
            $collection = [];
            foreach ($posts as $key => $post) {
                $this->post = $post;
                $collection[] = $this->getFields();
            }

            return $collection;
        }
    }

    public function getPosts(?array $postIn = null): array
    {
        $postStatus = [$this->postStatus];

        if ($this->take === 1 && is_user_logged_in()) {
            $postStatus[] = 'draft';
            $postStatus[] = 'private';
        }

        return get_posts([
            'post_type' => $this->postType,
            'name' => $this->postName,
            'posts_per_page' => $this->take,
            'post_status' => $postStatus,
            'offset' => $this->offset,
            'post__not_in' => $this->excludeIds,
            'post__in' => $postIn,
            'orderby' => $postIn ? 'post__in' : $this->orderBy,
            'order' => $this->order,
            'tax_query' => $this->taxQuery,
            's' => $this->search,
            'suppress_filters' => false,
            'meta_query' => $this->metaQuery,
        ]);
    }

    protected function getFields(): array
    {
        $fields = [];

        $fields['id'] = $this->post->ID;

        if ($this->title) {
            $fields['title'] = $this->getTitle();
        }

        if ($this->permalink) {
            $fields['permalink'] = $this->getPermalink();
        }

        if ($this->withDate) {
            $fields['date'] = $this->getPostDate();
        }

        if ($this->acf) {
            $fields = array_merge($fields, ACF::getFields($this->acf, $this->post));
        }

        return $fields;
    }

    protected function getPostDate()
    {
        return Carbon::parse(get_the_date('c', $this->post))
            ->locale(get_locale())
            ->settings(['formatFunction' => 'translatedFormat']);
    }

    protected function getTitle(): string
    {
        return html_entity_decode(get_the_title($this->post->ID), ENT_QUOTES);
    }

    protected function getPermalink(): string
    {
        return get_the_permalink($this->post->ID);
    }

    protected function prepareValueAndOperator($value, $operator, $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, '='];
        }

        return [$value, $operator];
    }
}
