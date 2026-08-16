<?php

namespace HumbleCore\Hook;

class FilterBuilder extends HookBuilder
{
    public function add(string $name, mixed $callback, int $priority = 10, int $acceptedArguments = 3): void
    {
        add_filter($name, $this->handleCallback($callback), $priority, $acceptedArguments);
    }

    public function remove(string $name, mixed $callback, int $priority = 10): void
    {
        remove_filter($name, $this->handleCallback($callback), $priority);
    }
}
