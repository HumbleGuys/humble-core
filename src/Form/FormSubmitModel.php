<?php

namespace HumbleCore\Form;

use HumbleCore\PostTypes\PostModel;

class FormSubmitModel extends PostModel
{
    public string $postType = 'form_submit';

    public $appends = ['content'];

    public function getContentAttribute()
    {
        return get_post_meta($this->id, 'content', true);
    }
}
