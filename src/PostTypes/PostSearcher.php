<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Facades\Action;

class PostSearcher {
    public $postType;

    public function __construct(PostType $postType)
    {
        $this->postType = $postType;

        Action::add('acf/save_post', [$this, 'handlePostUpdate'], 100);
    }

    public function handlePostUpdate($postId) {
        if (get_post_type($postId) === $this->postType->name) {
            $model = (new $this->postType->model)->find($postId)->withAcf()->withTitle()->first();

            $this->updateSearchText($model);
        }
    }

    public function updateSearchText($model) {
        $data = $this->postType->searchable($model);

        Action::remove('acf/save_post', [$this, 'handlePostUpdate'], 100);

        $content = collect($data)->join(' ');

        wp_update_post([
            'ID' => $model->id,
            'post_content' => $content
        ]);
    }
}