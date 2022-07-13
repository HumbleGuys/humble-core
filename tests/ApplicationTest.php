<?php

use HumbleCore\App\Application;

test('can boot app', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->isBooted())->toBe(false);

    $app->boot();

    expect($app->isBooted())->toBe(true);
});

test('default paths', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->basePath())->toBe(dirname(__DIR__));

    expect($app->configPath())->toBe(dirname(__DIR__).'/config');

    expect($app->publicPath())->toBe(dirname(__DIR__).'/public');

    expect($app->storagePath())->toBe(dirname(__DIR__).'/storage');

    expect($app->resourcePath())->toBe(dirname(__DIR__).'/resources');

    // Helpers

    expect(basePath())->toBe(dirname(__DIR__));

    expect(configPath())->toBe(dirname(__DIR__).'/config');

    expect(publicPath())->toBe(dirname(__DIR__).'/public');

    expect(storagePath())->toBe(dirname(__DIR__).'/storage');

    expect(resourcePath())->toBe(dirname(__DIR__).'/resources');
});

test('can change paths', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->basePath())->toBe(dirname(__DIR__));
    expect($app->configPath())->toBe(dirname(__DIR__).'/config');
    expect($app->publicPath())->toBe(dirname(__DIR__).'/public');
    expect($app->storagePath())->toBe(dirname(__DIR__).'/storage');
    expect($app->resourcePath())->toBe(dirname(__DIR__).'/resources');

    $app->setBasePath(__DIR__.DIRECTORY_SEPARATOR.'my-dir');
    $app->setConfigPath(basePath('/custom/path/config'));
    $app->setPublicPath(basePath('/public_html'));
    $app->setStoragePath(basePath('/custom/path/storage'));
    $app->setResourcePath(basePath('/custom/path/resources'));

    expect($app->basePath())->toBe(__DIR__.DIRECTORY_SEPARATOR.'my-dir');
    expect($app->configPath())->toBe(basePath('/custom/path/config'));
    expect($app->publicPath())->toBe(basePath('/public_html'));
    expect($app->storagePath())->toBe(basePath('/custom/path/storage'));
    expect($app->resourcePath())->toBe(basePath('/custom/path/resources'));
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
