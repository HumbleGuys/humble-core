<?php

namespace HumbleCore\Form;

use HumbleCore\PostTypes\PostType;

class FormSubmitPostType extends PostType
{
    public string $name = 'form_submit';

    public array $labels = [
        'name' => 'Form submits',
        'singular_name' => 'Form submit',
    ];

    public bool $public = false;
}
