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
        ->and($result->headers->get('Access-Control-Allow-Origin'))->toBe('*');
});

it('does not overwrite explicit API response policies', function (): void {
    $response = new Response('Private', 200, [
        'Access-Control-Allow-Origin' => 'https://contest.example',
        'Cache-Control' => 'private, no-store',
    ]);

    $result = (new ApiRouteResultHandler)->toResponse($response);

    expect($result)->toBe($response)
        ->and($result->headers->get('Access-Control-Allow-Origin'))->toBe('https://contest.example')
        ->and($result->headers->get('Cache-Control'))->toContain('private')
        ->and($result->headers->get('Cache-Control'))->toContain('no-store');
});

it('preserves an explicit empty API response', function (): void {
    $response = new Response(status: 204);

    $result = (new ApiRouteResultHandler)->toResponse($response);

    expect($result)->toBe($response)
        ->and($result->getStatusCode())->toBe(204)
        ->and($result->getContent())->toBeEmpty()
        ->and($result->headers->get('Access-Control-Allow-Origin'))->toBe('*');
});

it('rejects a null API route result', function (): void {
    (new ApiRouteResultHandler)->toResponse(null);
})->throws(UnexpectedValueException::class, 'API route actions must return a response value.');

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
