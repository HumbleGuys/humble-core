<?php

declare(strict_types=1);

use HumbleCore\Console\Command;
use HumbleCore\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

it('dispatches a registered synchronous command', function (): void {
    $kernel = new class extends Kernel
    {
        protected array $commands = [
            'test:command' => SuccessfulConsoleCommand::class,
        ];
    };
    $output = new BufferedOutput;

    $status = $kernel->handle(
        new ArgvInput(['humble', 'test:command']),
        $output,
    );

    expect($status)->toBe(Command::SUCCESS)
        ->and($output->fetch())->toBe("Running command: test:command\nCommand completed.\n");
});

it('returns an invalid exit code for unknown or missing commands', function (array $arguments, string $name): void {
    $output = new BufferedOutput;

    $status = (new Kernel)->handle(new ArgvInput($arguments), $output);

    expect($status)->toBe(Command::INVALID)
        ->and($output->fetch())->toBe("The command '{$name}' does not exist.\n");
})->with([
    'unknown command' => [['humble', 'unknown'], 'unknown'],
    'missing command' => [['humble'], ''],
]);

final class SuccessfulConsoleCommand extends Command
{
    public function handle(): int
    {
        $this->output->writeln('Command completed.');

        return self::SUCCESS;
    }
}
