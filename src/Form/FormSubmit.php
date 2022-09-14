<?php

namespace HumbleCore\Form;

class FormSubmit
{
    public static function create(string $title, string $content)
    {
        $postId = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'form_submit',
            'post_status' => 'private',
        ]);

        add_post_meta($postId, 'content', $content);
    }

    public static function remove($id)
    {
        if (get_post_type($id) !== 'form_submit') {
            return;
        }

        return wp_trash_post($id);
    }
}
