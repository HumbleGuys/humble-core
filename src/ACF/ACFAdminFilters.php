<?php

namespace HumbleCore\ACF;

class ACFAdminFilters
{
    public static function relationshipQuery($args, $field, $postId)
    {
        $args['post_status'] = ['publish']; // Only show published posts
        $args['post__not_in'] = [$postId]; // Dont show current post

        return $args;
    }
}
