<?php

namespace HumbleCore\Hook;

use ReflectionMethod;
use UnexpectedValueException;

class HookBuilder
{
    public function handleCallback(mixed $callback)
    {
        if (is_callable($callback)) {
            return $callback;
        }

        if (is_array($callback) && is_string($callback[0])) {
            $reflection = new ReflectionMethod($callback[0], $callback[1]);

            if (! $reflection->isStatic()) {
                $callback[0] = new $callback[0];

                return $callback;
            }
        }

        if (is_string($callback) && method_exists($callback, '__invoke')) {
            return [new $callback, '__invoke'];
        }

        throw new UnexpectedValueException('Invalid action for hook');
    }
}
