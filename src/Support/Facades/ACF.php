<?php

namespace HumbleCore\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, mixed> getFields(array<int, string>|bool $fields, mixed $post)
 */
class ACF extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'acf';
    }
}
