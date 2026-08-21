<?php

namespace HumbleCore\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Kernel
{
    /** @var array<string, class-string<Command>> */
    protected array $commands = [];

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getFirstArgument();

        if ($name === null || ! isset($this->commands[$name])) {
            $output->writeln("The command '{$name}' does not exist.");

            return Command::INVALID;
        }

        $output->writeln("Running command: {$name}");

        $commandClass = $this->commands[$name];
        $command = new $commandClass($input, $output);

        return $command->handle();
    }
}
