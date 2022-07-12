<?php

use HumbleCore\App\Application;

test('can write debug to log file', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'logging' => [
            'default' => 'single',

            'channels' => [
                'single' => [
                    'driver' => 'single',
                    'path' => storagePath('logs/app.log'),
                    'level' => env('LOG_LEVEL', 'debug'),
                ],
            ],
        ],
    ]);

    $app->boot();

    logger('Humble Guys Debug Message');

    $file = storagePath('logs/app.log');

    $content = file_get_contents($file);

    expect($content)->toContain('Humble Guys Debug Message');
});

test('can write info to log file', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'logging' => [
            'default' => 'single',

            'channels' => [
                'single' => [
                    'driver' => 'single',
                    'path' => storagePath('logs/app.log'),
                    'level' => env('LOG_LEVEL', 'debug'),
                ],
            ],
        ],
    ]);

    $app->boot();

    info('Humble Guys Info Message');

    $file = storagePath('logs/app.log');

    $content = file_get_contents($file);

    expect($content)->toContain('Humble Guys Info Message');
});
