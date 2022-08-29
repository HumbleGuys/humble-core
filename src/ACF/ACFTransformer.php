<?php

namespace HumbleCore\ACF;

use Carbon\Carbon;

class ACFTransformer
{
    public static function date($date)
    {
        if (empty($date)) {
            return;
        }

        return Carbon::parse($date)
            ->locale(get_locale())
            ->settings(['formatFunction' => 'translatedFormat']);
    }

    public static function flexibleContent($rows)
    {
        if (empty($rows)) {
            return collect();
        }

        return collect($rows)->map(function ($row) {
            return new FlexibleContentRow($row);
        });
    }

    public static function file($file)
    {
        if (empty($file)) {
            return;
        }

        if (is_string($file)) {
            return $file;
        }

        return (object) $file;
    }

    public static function googleMap($googleMap)
    {
        if (empty($googleMap)) {
            return;
        }

        return (object) $googleMap;
    }

    public static function gallery($images)
    {
        if (empty($images)) {
            return collect();
        }

        return collect($images)->map(function ($image) {
            return self::image($image);
        });
    }

    public static function group($group)
    {
        if (empty($group)) {
            return;
        }

        return (object) $group;
    }

    public static function image($image)
    {
        if (empty($image)) {
            return;
        }

        if (is_string($image)) {
            return $image;
        }

        if (empty($image['sizes'])) {
            return;
        }

        $sizes = $image['sizes'];
        unset($sizes['thumbnail']);
        unset($sizes['thumbnail-width']);
        unset($sizes['thumbnail-height']);

        return new Image($image['url'], $image['alt'], $image['caption'], $sizes);
    }

    public static function link($link)
    {
        if (empty($link)) {
            return;
        }

        if (is_string($link)) {
            return $link;
        }

        return (object) $link;
    }

    public static function relationship($posts)
    {
        if (empty($posts)) {
            return collect();
        }

        return collect($posts)->map(function ($post) {
            if (is_object($post)) {
                $post = $post->ID;
            }

            $postType = app('postTypes')->getPostType(get_post_type($post));

            if (empty($postType->model)) {
                return $post;
            }

            $model = (new $postType->model)->find($post);

            if (method_exists($model, 'fromRelation')) {
                $model->fromRelation();
            }

            return $model->first();
        });
    }

    public static function repeater($rows)
    {
        if (empty($rows)) {
            return collect();
        }

        return collect($rows)->map(function ($row) {
            return (object) $row;
        });
    }
}
