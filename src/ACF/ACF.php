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

    public function getFields($fields, $post)
    {
        if (is_array($fields)) {
            return $this->getFieldsFromArray($fields, $post);
        }

        return $this->getAllFields($post);
    }

    protected function getAllFields($post): array
    {
        if (! function_exists('get_fields')) {
            return [];
        }

        return get_fields($post) ?: [];
    }

    protected function getFieldsFromArray(array $fields, $post): array
    {
        return array_reduce($fields, function (array $acc, string $field) use ($post) {
            $acc[$field] = $this->get($field, $post);

            return $acc;
        }, []);
    }
}
