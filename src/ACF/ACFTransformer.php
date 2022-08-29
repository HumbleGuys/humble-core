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
