<?php

declare(strict_types=1);

use HumbleCore\Routing\Route;
use HumbleCore\Routing\Router;
use Illuminate\Http\Response;
use Tests\Support\WordPressRoutingFunctions;

require_once __DIR__.'/Support/WordPressRoutingFunctions.php';

beforeEach(function (): void {
    WordPressRoutingFunctions::reset();

    $GLOBALS['wp_query'] = new class
    {
        public bool $is_404 = false;

        public function set_404(): void
        {
            $this->is_404 = true;
        }
    };
});

afterEach(function (): void {
    unset($GLOBALS['wp_query']);
});

it('falls back to the registered 404 route with WordPress state and headers set before the controller runs', function (): void {
    $route = routerTestRoute('404', function (): string {
        expect($GLOBALS['wp_query']->is_404)->toBeTrue()
            ->and(WordPressRoutingFunctions::$statusCodes)->toBe([404])
            ->and(WordPressRoutingFunctions::$noCacheHeadersSent)->toBeTrue();

        return 'Custom not-found page';
    });
    $router = new TestWordPressRouter([$route]);

    expect($router->initWp('unused-template.php'))->toBe('Custom not-found page')
        ->and($router->getCurrentRoute())->toBe($route);
});

it('resolves a matching route without triggering the fallback', function (): void {
    $page = routerTestRoute('page', fn (): string => 'Page content', matches: true);
    $notFound = routerTestRoute('404', function (): never {
        throw new RuntimeException('The fallback should not run');
    });
    $router = new TestWordPressRouter([$page, $notFound]);

    expect($router->initWp('unused-template.php'))->toBe('Page content')
        ->and($router->getCurrentRoute())->toBe($page)
        ->and($GLOBALS['wp_query']->is_404)->toBeFalse()
        ->and(WordPressRoutingFunctions::$statusCodes)->toBeEmpty()
        ->and(WordPressRoutingFunctions::$noCacheHeadersSent)->toBeFalse();
});

it('returns a generic 404 when no WordPress 404 route is registered', function (): void {
    $apiRoute = new Route('GET', '404', function (): never {
        throw new RuntimeException('An HTTP route must not be used as the WordPress fallback');
    });
    $router = new TestWordPressRouter([$apiRoute]);

    $response = $router->initWp('unused-template.php');

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(404)
        ->and($response->getContent())->toBe('Not Found')
        ->and($response->headers->get('Cache-Control'))->toContain('no-cache')
        ->and($router->getCurrentRoute())->toBeNull()
        ->and($GLOBALS['wp_query']->is_404)->toBeTrue();
});

it('propagates controller failures instead of treating them as missing routes', function (bool $matches): void {
    $exception = new RuntimeException('Controller failed');
    $route = routerTestRoute($matches ? 'page' : '404', function () use ($exception): never {
        throw $exception;
    }, $matches);
    $router = new TestWordPressRouter([$route]);

    expect(fn () => $router->initWp('unused-template.php'))->toThrow($exception);
})->with([
    'matching route' => [true],
    '404 fallback' => [false],
]);

it('propagates invalid fallback handlers as configuration errors', function (): void {
    $router = new TestWordPressRouter([routerTestRoute('404', new stdClass)]);

    $router->initWp('unused-template.php');
})->throws(UnexpectedValueException::class, 'Invalid route action for: [404].');

function routerTestRoute(string $path, mixed $handler, bool $matches = false): Route
{
    $route = test()->getMockBuilder(Route::class)
        ->setConstructorArgs(['WP', $path, $handler])
        ->onlyMethods(['isMatching', 'getWpIdForRoute'])
        ->getMock();
    $route->method('isMatching')->willReturn($matches);
    $route->method('getWpIdForRoute')->willReturn(null);

    return $route;
}

final class TestWordPressRouter extends Router
{
    /** @param list<Route> $routes */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
}
