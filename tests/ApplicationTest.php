<?php

declare(strict_types=1);

use HumbleCore\App\Application;

it('boots configured providers through the application lifecycle', function (): void {
    $app = $this->bootApplication();

    expect($app->isBooted())->toBeTrue()
        ->and(app())->toBe($app)
        ->and($app->publicPath())->toBe(dirname(__DIR__).'/public_html');
});

it('uses production-safe defaults when environment variables are missing', function (): void {
    unset($_ENV['APP_ENV'], $_ENV['APP_DEBUG']);
    $_ENV['APP_RUNNING_IN_CONSOLE'] = 'false';

    try {
        $this->app = new Application(dirname(__DIR__), dirname(__DIR__));

        expect($this->app->isProduction())->toBeTrue()
            ->and($this->app->isLocal())->toBeFalse();
    } finally {
        unset($_ENV['APP_RUNNING_IN_CONSOLE']);
    }
});
