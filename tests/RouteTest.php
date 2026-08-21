<?php

declare(strict_types=1);

use HumbleCore\Routing\Route;
use Illuminate\Http\Request;

if (! function_exists('get_home_url')) {
    function get_home_url(): string
    {
        return 'https://example.test';
    }
}

it('rejects an unknown WordPress route type with a routing exception', function (): void {
    (new Route('WP', 'unknown', static fn (): null => null))->url(null);
})->throws(UnexpectedValueException::class, 'Unknown route type.');

it('generates URLs for parameterized API routes', function (): void {
    $route = new Route('GET', '/api/store-offers/{id}', static fn (): null => null);

    expect($route->url(42))->toBe('https://example.test/api/store-offers/42/')
        ->and($route->url('south west'))->toBe('https://example.test/api/store-offers/south%20west/');
});

it('matches parameterized routes and extracts decoded arguments', function (string $uri, string $argument): void {
    $this->bootApplication();
    $this->app->instance('request', Request::create($uri, 'GET'));

    $route = new Route('GET', '/api/store-offers/{id}', static fn (): null => null);

    expect($route->isMatching())->toBeTrue()
        ->and($route->parameters())->toBe([$argument]);
})->with([
    'query string' => ['/api/store-offers/south%20west?preview=1', 'south west'],
    'trailing slash' => ['/api/store-offers/south%20west/', 'south west'],
    'zero value' => ['/api/store-offers/0', '0'],
]);

it('requires the exact number of route segments', function (): void {
    $this->bootApplication();
    $this->app->instance('request', Request::create('/api/store-offers/42/details', 'GET'));

    $route = new Route('GET', '/api/store-offers/{id}', static fn (): null => null);

    expect($route->isMatching())->toBeFalse();
});

it('resolves supported route action forms with forwarded arguments', function (): void {
    $this->bootApplication();

    $closure = new Route('GET', '/closure/{id}', static fn (string $id): string => "closure:{$id}");
    $controller = new Route('GET', '/controller/{id}', [RouteTestController::class, 'show']);
    $invokable = new Route('GET', '/invokable/{id}', InvokableRouteTestController::class);

    expect($closure->resolve(['10']))->toBe('closure:10')
        ->and($controller->resolve(['11']))->toBe('controller:11')
        ->and($invokable->resolve(['12']))->toBe('invokable:12');
});

it('rejects an invalid route action', function (): void {
    (new Route('GET', '/invalid', new stdClass))->resolve();
})->throws(UnexpectedValueException::class, 'Invalid route action for: [/invalid].');

final class RouteTestController
{
    public function show(string $id): string
    {
        return "controller:{$id}";
    }
}

final class InvokableRouteTestController
{
    public function __invoke(string $id): string
    {
        return "invokable:{$id}";
    }
}
