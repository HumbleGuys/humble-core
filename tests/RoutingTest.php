<?php

use HumbleCore\App\Application;
use HumbleCore\Routing\Route as RoutingRoute;
use HumbleCore\Routing\RoutingServiceProvider;
use Illuminate\Support\Facades\Route;

WP_Mock::bootstrap();

beforeAll(function () {
    $app = new Application(dirname(__DIR__));

    config([
        'app' => [
            'providers' => [
                RoutingServiceProvider::class,
            ],
        ],
    ]);

    $app->boot();
});

test('can register wp routes', function () {
    Route::wp('page', function () {
        return 'Hello Page!';
    });

    expect(app()->router->getRoutes()[0])->toBeInstanceOf(RoutingRoute::class);

    Route::wp('front-page', function () {
        return 'Hello Front Page!';
    });

    expect(app()->router->getRoutes())->toHaveCount(2);
});

test('can match page route', function () {
    $route = app()->router->getRoutes()[0];

    \WP_Mock::userFunction('is_home', [
        'return' => false,
    ]);

    \WP_Mock::userFunction('is_singular', [
        'return' => false,
    ]);

    \WP_Mock::userFunction('is_front_page', [
        'return' => false,
    ]);

    \WP_Mock::userFunction('is_post_type_archive', [
        'return' => false,
    ]);

    \WP_Mock::userFunction('is_tax', [
        'return' => false,
    ]);

    \WP_Mock::userFunction('is_page_template', [
        'return' => false,
    ]);

    \WP_Mock::userFunction('is_page', [
        'return' => true,
    ]);

    expect($route->isMatching())->toBeTrue();
});

test('can resolve route with callback handler', function () {
    $route = app()->router->getRoutes()[0];

    expect($route->resolve())->toBe('Hello Page!');
});

test('can resolve route with array handler', function () {
    class PostController
    {
        public function show()
        {
            return 'Hello Post!';
        }
    }

    Route::wp('post', [PostController::class, 'show']);

    $route = app()->router->getRoutes()[2];

    expect($route->resolve())->toBe('Hello Post!');
});
