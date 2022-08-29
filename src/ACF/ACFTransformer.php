<?php

namespace HumbleCore\ACF;

class ACFTransformer
{
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
