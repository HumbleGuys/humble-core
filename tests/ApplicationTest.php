<?php

use HumbleCore\App\Application;

test('default paths', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->basePath())->toBe(dirname(__DIR__));

    expect($app->bootstrapPath())->toBe(dirname(__DIR__) . '/bootstrap');
    
    expect($app->configPath())->toBe(dirname(__DIR__) . '/config');

    expect($app->publicPath())->toBe(dirname(__DIR__) . '/public');

    expect($app->storagePath())->toBe(dirname(__DIR__) . '/storage');

    expect($app->resourcePath())->toBe(dirname(__DIR__) . '/resources');
});

test('is running in console', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->runningInConsole())->toBe(true);
});