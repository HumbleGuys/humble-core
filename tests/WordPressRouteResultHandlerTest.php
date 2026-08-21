<?php

declare(strict_types=1);

use HumbleCore\Routing\WordPressRouteResultHandler;
use HumbleCore\View\ViewServiceProvider;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Response;

it('renders supported WordPress route results', function (mixed $result, string $expected): void {
    $handler = new WordPressRouteResultHandler;

    ob_start();
    $handler->send($result);
    $output = ob_get_clean();

    expect($output)->toBe($expected);
})->with([
    'string' => ['Plain route result', 'Plain route result'],
    'renderable' => [new class implements Renderable
    {
        public function render(): string
        {
            return 'Rendered route result';
        }
    }, 'Rendered route result'],
    'stringable' => [new class implements Stringable
    {
        public function __toString(): string
        {
            return 'Stringable route result';
        }
    }, 'Stringable route result'],
]);

it('preserves an existing response', function (): void {
    $handler = new WordPressRouteResultHandler;
    $response = new Response('Response body', 202, ['X-Test' => 'preserved']);

    ob_start();
    $handler->send($response);
    $output = ob_get_clean();

    expect($output)->toBe('Response body')
        ->and($response->getStatusCode())->toBe(202)
        ->and($response->headers->get('X-Test'))->toBe('preserved');
});

it('renders a lazy view when the handler consumes it', function (): void {
    $compiledPath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/views';

    if (! is_dir($compiledPath)) {
        mkdir($compiledPath, 0777, true);
    }

    $this->bootApplication([ViewServiceProvider::class], [
        'view' => [
            'paths' => [dirname(__DIR__).'/tests/resources/views'],
            'compiled' => $compiledPath,
        ],
    ]);

    $sideEffectPath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/handler-view-'.uniqid('', true);
    $view = view('lazyView', ['sideEffectPath' => $sideEffectPath]);

    expect(is_file($sideEffectPath))->toBeFalse();

    ob_start();
    (new WordPressRouteResultHandler)->send($view);
    $output = ob_get_clean();

    expect($output)->toBe("Rendered lazily\n")
        ->and(file_get_contents($sideEffectPath))->toBe('rendered');
});

it('rejects unsupported WordPress route results', function (): void {
    (new WordPressRouteResultHandler)->send(['unsupported']);
})->throws(UnexpectedValueException::class);

it('propagates render exceptions', function (): void {
    $result = new class implements Renderable
    {
        public function render(): string
        {
            throw new RuntimeException('render failed');
        }
    };

    (new WordPressRouteResultHandler)->send($result);
})->throws(RuntimeException::class, 'render failed');
