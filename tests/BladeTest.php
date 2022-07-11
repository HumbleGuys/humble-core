<?php

use HumbleCore\App\Application;
use Illuminate\Support\Facades\Blade;

test('can render blade string', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'view' => [
            'paths' => [
                app()->resourcePath(),
            ],

            'compiled' => app()->storagePath('cache'),
        ],
    ]);

    $res = Blade::render('Hello, {{ $name }}', ['name' => 'Humble Guys']);

    expect($res)->toBe('Hello, Humble Guys');
});

test('can render blade view', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'view' => [
            'paths' => [
                app()->basePath('tests/resources/views'),
            ],

            'compiled' => app()->storagePath('cache'),
        ],
    ]);

    expect(view('testView', ['name' => 'Humble Guys']))->toBe('<div>Humble Guys</div>');
});
