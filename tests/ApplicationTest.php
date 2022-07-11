<?php

use HumbleCore\App\Application;

test('default paths', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->basePath())->toBe(dirname(__DIR__));

    expect($app->bootstrapPath())->toBe(dirname(__DIR__).'/bootstrap');

    expect($app->configPath())->toBe(dirname(__DIR__).'/config');

    expect($app->publicPath())->toBe(dirname(__DIR__).'/public');

    expect($app->storagePath())->toBe(dirname(__DIR__).'/storage');

    expect($app->resourcePath())->toBe(dirname(__DIR__).'/resources');
});

test('app helper method returns application', function () {
    $app = new Application(dirname(__DIR__));

    $appFromHelper = app();

    expect($app)->toBe($appFromHelper);
});

test('is running in console', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->runningInConsole())->toBe(true);
});

test('config', function () {
    new Application(dirname(__DIR__));

    config(['test' => 'myvalue']);

    expect(config('test'))->toBe('myvalue');

    expect(config('dont_exists'))->toBe(null);
    expect(config('test.dont_exists'))->toBe(null);

    config(['test_nested' => [
        'nested_key' => 'lorem',
    ]]);

    expect(config('test_nested.nested_key'))->toBe('lorem');
});
