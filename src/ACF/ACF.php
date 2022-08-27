<?php

namespace HumbleCore\ACF;

class ACF
{
    public function get(string $name, string|int $postId)
    {
        if (! function_exists('get_field')) {
            return;
        }

        return get_field($name, $postId);
    }
}
