<?php

namespace HumbleCore\Hook;

class ActionBuilder extends HookBuilder
{
    public function add(string $name, mixed $callback, int $priority = 10, int $acceptedArguments = 3)
    {
        add_action($name, $this->handleCallback($callback), $priority, $acceptedArguments);
    }

    public function remove(string $name, mixed $callback, int $priority = 10)
    {
        remove_action($name, $this->handleCallback($callback), $priority);
    }
}
