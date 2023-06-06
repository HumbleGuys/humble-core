<?php

namespace HumbleCore\Console;

class Command
{
    public const SUCCESS = 0;

    public const FAILURE = 1;

    public const INVALID = 2;

    public function __construct(public $input, public $output)
    {
    }

    protected function resolveStubPath($stub)
    {
        $path = templatePath('vendor\humble-guys\humble-core\src\Console');

        return $path.$stub;
    }
}
