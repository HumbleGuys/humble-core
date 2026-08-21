<?php

namespace HumbleCore\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command
{
    public const SUCCESS = 0;

    public const FAILURE = 1;

    public const INVALID = 2;

    public function __construct(
        public InputInterface $input,
        public OutputInterface $output,
    ) {}

    abstract public function handle(): int;

    protected function resolveStubPath($stub)
    {
        $path = __DIR__.'/stubs';

        return $path.$stub;
    }
}
