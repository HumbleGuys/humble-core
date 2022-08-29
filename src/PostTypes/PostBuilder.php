<?php

namespace HumbleCore\PostTypes;

use Carbon\Carbon;
use HumbleCore\Support\Facades\ACF;

class PostBuilder
{
    private $findId;

    private $take = -1;

    private $excludeIds;

    private $title;

    private $permalink;

    private $withDate;

    private $relativePermalink = false;

    private $acf;

    private $taxQuery;

    private $offset = 0;

    private $orderBy = 'post_date';

    private $order = 'desc';

    private $orderMetaKey;

    private $search;

    private $postStatus = 'publish';

    private $postName;

    private $metaQuery = [
        'relation' => 'AND',
        'metaFilters' => [],
    ];

    protected $postType;

    protected $post;

    protected $model;

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

    public function orderBy(string $field, string $order = 'desc', string $orderBy = 'meta_value'): PostModel
    {
        $this->orderMetaKey = $field;
        $this->order = $order;
        $this->orderBy = $orderBy;

        return $this->model;
    }

    public function orderByMetaQuery(array $metas): PostModel
    {
        $this->orderBy = [];

        foreach ($metas as $key => $meta) {
            $this->metaQuery[$key] = [
                'key' => $key,
                'type' => $meta['type'],
            ];

            $this->orderBy[$key] = $meta['order'];
        }

        return $this->model;
    }

    public function orderByAsc(string $field): PostModel
    {
        return $this->orderBy($field, 'asc');
    }

    public function orderByDesc(string $field): PostModel
    {
        return $this->orderBy($field, 'desc');
    }

    public function orderByNum(string $field, string $order = 'desc'): PostModel
    {
        return $this->orderBy($field, $order, 'meta_value_num');
    }

    public function orderByNumAsc(string $field): PostModel
    {
        return $this->orderByNum($field, 'asc');
    }

    public function orderByNumDesc(string $field): PostModel
    {
        return $this->orderByNum($field, 'desc');
    }

    public function orderBySortOrder(string $order = 'asc')
    {
        $this->orderBy = 'menu_order';
        $this->order = $order;

        return $this->model;
    }

    public function orderByRandom()
    {
        $this->orderBy = 'rand';
        $this->order = 'asc';

        return $this->model;
    }

    public function where($field, $operator = null, $value = null, $type = null, $relation = null)
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

    public function whereDate($field, $operator, $value, $relation = null)
    {
        return $this->where($field, $operator, $value, 'DATE', $relation);
    }

    public function orWhere($field, $operator = null, $value = null, $type = null)
    {
        return $this->where($field, $operator, $value, $type, 'OR');
    }

    public function orWhereDate($field, $operator, $value)
    {
        return $this->whereDate(...[...func_get_args(), 'OR']);
    }

    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        }

        return [$value, $operator];
    }

    public function whereTax(?array $items, string $field = 'term_id', string $relation = 'OR'): PostModel
    {
        if (! $items) {
            return $this->model;
        }

        $query = ['relation ' => $relation];

        foreach ($items as $item) {
            $query[] = [
                'taxonomy' => $item['taxonomy'],
                'field' => $field,
                'terms' => $item['id'],
            ];
        }

        if ($this->taxQuery) {
            $this->taxQuery = array_merge($query, $this->taxQuery);
        } else {
            $this->taxQuery = $query;
        }

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

    public function withPermalink(bool $relative = false): PostModel
    {
        $this->permalink = true;
        $this->relativePermalink = $relative;

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
        return $this->model::hydrate($this->getItems(), $this->model->getAppends(), $this->model->getMutators());
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

    protected function getPosts(?array $postIn = null): array
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
            'meta_key' => $this->orderMetaKey,
            'orderby' => $postIn ? 'post__in' : $this->orderBy,
            'order' => $this->order,
            'tax_query' => $this->taxQuery,
            'meta_query' => $this->metaQuery,
            's' => $this->search,
            'suppress_filters' => false,
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
        $link = get_the_permalink($this->post->ID);

        if ($this->relativePermalink) {
            $link = wp_make_link_relative($link);
        }

        return $link;
    }
}
