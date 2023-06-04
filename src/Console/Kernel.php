<?php

namespace HumbleCore\Console;

use Illuminate\Contracts\Console\Kernel as KernelContract;

class Kernel implements KernelContract
{
    protected $commands = [];

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {
    }

    /**
     * Handle an incoming console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        $name = $input->getFirstArgument();

        if (! isset($this->commands[$name])) {
            $output->writeln("The command '{$name}' doesnt exists");

            return 0;
        }

        $output->writeln("Running command: {$name}");

        $command = new $this->commands[$name]($input, $output);

        return $command->handle();
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string  $command
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
    }

    /**
     * Queue an Artisan console command by name.
     *
     * @param  string  $command
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function queue($command, array $parameters = [])
    {
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
    }

    /**
     * Terminate the application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     * @return void
     */
    public function terminate($input, $status)
    {
    }
}
