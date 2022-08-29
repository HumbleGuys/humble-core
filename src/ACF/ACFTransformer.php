<?php

namespace HumbleCore\ACF;

class ACFTransformer
{
    public static function googleMap($googleMap)
    {
        if (empty($googleMap)) {
            return;
        }

        return (object) $googleMap;
    }

    public static function group($group)
    {
        if (empty($group)) {
            return;
        }

        return (object) $group;
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
