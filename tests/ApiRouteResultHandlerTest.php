<?php

declare(strict_types=1);

use HumbleCore\Routing\ApiRouteResultHandler;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Response;

it('creates the default API response for controller results', function (): void {
    $result = new class implements Renderable
    {
        public function render(): string
        {
            return 'Rendered API view';
        }
    };

    $response = (new ApiRouteResultHandler)->toResponse($result);

    expect($response->getContent())->toBe('Rendered API view')
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Access-Control-Allow-Origin'))->toBe('*');
});

it('converts arrays to JSON with the default headers', function (): void {
    $response = (new ApiRouteResultHandler)->toResponse(['success' => true]);

    expect($response->getContent())->toBe('{"success":true}')
        ->and($response->headers->get('Content-Type'))->toStartWith('application/json')
        ->and($response->headers->get('Cache-Control'))->toBe('public')
        ->and($response->headers->get('Access-Control-Allow-Origin'))->toBe('*');
});

it('preserves an existing API response', function (): void {
    $response = new Response('Created', 201, ['X-Test' => 'preserved']);

    $result = (new ApiRouteResultHandler)->toResponse($response);

    expect($result)->toBe($response)
        ->and($result->getStatusCode())->toBe(201)
        ->and($result->headers->get('X-Test'))->toBe('preserved')
        ->and($result->headers->has('Access-Control-Allow-Origin'))->toBeFalse();
});

it('propagates render exceptions', function (): void {
    $result = new class implements Renderable
    {
        public function render(): string
        {
            throw new RuntimeException('render failed');
        }
    };

    (new ApiRouteResultHandler)->toResponse($result);
})->throws(RuntimeException::class, 'render failed');
