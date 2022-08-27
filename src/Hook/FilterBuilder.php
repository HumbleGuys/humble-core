<?php

namespace HumbleCore\Hook;

class FilterBuilder
{
    public function add(string $name, callable|array $callback, int $priority = 10, int $acceptedArguments = 3)
    {
        add_filter($name, $callback, $priority, $acceptedArguments);
    }
}
