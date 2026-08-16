<?php

namespace HumbleCore\Support\Facades;

use HumbleCore\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RouteDefinition delete(string $path, mixed $handler)
 * @method static RouteDefinition get(string $path, mixed $handler)
 * @method static RouteDefinition post(string $path, mixed $handler)
 * @method static RouteDefinition put(string $path, mixed $handler)
 * @method static RouteDefinition wp(string $path, mixed $handler)
 */
class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'router';
    }
}
