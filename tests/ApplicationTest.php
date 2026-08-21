<?php

declare(strict_types=1);

it('boots configured providers through the application lifecycle', function (): void {
    $app = $this->bootApplication();

    expect($app->isBooted())->toBeTrue()
        ->and(app())->toBe($app)
        ->and($app->publicPath())->toBe(dirname(__DIR__).'/public_html');
});
