<?php

namespace HumbleCore\Console;

class Command
{
    public function __construct(public $input, public $output)
    {
    }

    protected function resolveStubPath($stub)
    {
        $path = templatePath('vendor\humble-guys\humble-core\src\Console');

        return $path.$stub;
    }
}
