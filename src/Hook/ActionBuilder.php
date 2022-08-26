<?php

namespace HumbleCore\Hook;

class ActionBuilder
{
    public function add(string $name, callable $callback, int $priority = 10, int $acceptedArguments = 3)
    {
        add_action($name, $callback, $priority, $acceptedArguments);
    }
}
