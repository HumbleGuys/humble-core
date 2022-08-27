<?php

namespace HumbleCore\Hook;

class FilterBuilder extends HookBuilder
{
    public function add(string $name, mixed $callback, int $priority = 10, int $acceptedArguments = 3)
    {
        add_filter($name, $this->handleCallback($callback), $priority, $acceptedArguments);
    }
}
