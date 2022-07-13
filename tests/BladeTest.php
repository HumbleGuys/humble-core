<?php

use HumbleCore\App\Application;
use HumbleCore\View\ViewServiceProvider;
use Illuminate\Support\Facades\Blade;

test('can render blade string', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'app' => [
            'providers' => [
                ViewServiceProvider::class,
            ],
        ],

        'view' => [
            'paths' => [
                app()->resourcePath(),
            ],

            'compiled' => app()->storagePath('cache'),
        ],
    ]);

    $app->boot();

    $res = Blade::render('Hello, {{ $name }}', ['name' => 'Humble Guys']);

    expect($res)->toBe('Hello, Humble Guys');
});

test('can render blade view', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'app' => [
            'providers' => [
                ViewServiceProvider::class,
            ],
        ],

        'view' => [
            'paths' => [
                app()->basePath('tests/resources/views'),
            ],

            'compiled' => app()->storagePath('cache'),
        ],
    ]);

    $app->boot();

    expect(view('testView', ['name' => 'Humble Guys']))->toBe('<div>Humble Guys</div>');
});

test('can render blade view from folder name if nested', function () {
    $app = new Application(dirname(__DIR__));

    config([
        'app' => [
            'providers' => [
                ViewServiceProvider::class,
            ],
        ],

        'view' => [
            'paths' => [
                app()->basePath('tests/resources/views'),
            ],

            'compiled' => app()->storagePath('cache'),
        ],
    ]);

    $app->boot();

    expect(view('nestedView', ['name' => 'Humble Guys Nested']))->toBe('<div>Humble Guys Nested</div>');
});
