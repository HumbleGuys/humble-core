<?php

declare(strict_types=1);

use HumbleCore\View\ViewServiceProvider;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

beforeEach(function (): void {
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
});

it('returns an inspectable view without rendering it eagerly', function (): void {
    $view = view('testView', ['name' => 'Humble Guys']);

    expect($view)
        ->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('testView')
        ->and($view->getData())->toMatchArray(['name' => 'Humble Guys'])
        ->and($view->render())->toBe('<div>Humble Guys</div>');
});

it('does not render a view until render is called', function (): void {
    $sideEffectPath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/lazy-view-'.uniqid('', true);

    $view = view('lazyView', ['sideEffectPath' => $sideEffectPath]);

    expect(is_file($sideEffectPath))->toBeFalse();

    expect($view->render())->toBe("Rendered lazily\n");
    expect(file_get_contents($sideEffectPath))->toBe('rendered');
});

it('returns the view factory when called without arguments', function (): void {
    expect(view())->toBeInstanceOf(Factory::class);
});

it('finds a view whose file is nested in a matching directory', function (): void {
    $view = view('nestedView', ['name' => 'Nested view']);

    expect($view->getPath())->toEndWith('/nestedView/nestedView.blade.php')
        ->and($view->render())->toBe('<div>Nested view</div>');
});

it('resolves regular dotted view names before applying the nested fallback', function (): void {
    $view = view('emails.message', [
        'rows' => [['value' => 'Message body']],
    ]);

    expect($view->getPath())->toEndWith('/emails/message.blade.php')
        ->and($view->render())->toContain('Message body');
});
