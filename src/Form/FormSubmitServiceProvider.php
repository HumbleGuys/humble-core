<?php

namespace HumbleCore\Form;

use Illuminate\Support\ServiceProvider;

class FormSubmitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $postType = new FormSubmitPostType;

        $postType->register();

        FormSubmitAdminPanel::register();
    }
}
