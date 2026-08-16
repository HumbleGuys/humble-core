<?php

namespace HumbleCore\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void add(string $name, mixed $callback, int $priority = 10, int $acceptedArguments = 3)
 * @method static void remove(string $name, mixed $callback, int $priority = 10)
 */
class Action extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'action';
    }
}
