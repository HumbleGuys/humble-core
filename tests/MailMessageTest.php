<?php

declare(strict_types=1);

use HumbleCore\Mail\Mailer;
use HumbleCore\Mail\MailMessage;
use HumbleCore\View\ViewServiceProvider;

it('renders the mail view to a string body', function (): void {
    $compiledPath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/views';

    if (! is_dir($compiledPath)) {
        mkdir($compiledPath, 0777, true);
    }

    $this->bootApplication([ViewServiceProvider::class], [
        'mail' => [
            'mailer' => 'log',
            'from' => [
                'name' => 'Humble Core',
                'address' => 'test@example.test',
            ],
        ],
        'view' => [
            'paths' => [dirname(__DIR__).'/tests/resources/views'],
            'compiled' => $compiledPath,
        ],
    ]);

    $body = (new MailMessage)->heading('Hello')->getBody();

    expect($body)->toBeString()->toContain('Hello');
});

it('renders a generic mail template to a string body', function (): void {
    $compiledPath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/views';

    if (! is_dir($compiledPath)) {
        mkdir($compiledPath, 0777, true);
    }

    $this->bootApplication([ViewServiceProvider::class], [
        'mail' => [
            'mailer' => 'log',
            'from' => [
                'name' => 'Humble Core',
                'address' => 'test@example.test',
            ],
        ],
        'view' => [
            'paths' => [dirname(__DIR__).'/tests/resources/views'],
            'compiled' => $compiledPath,
        ],
    ]);

    $body = (new Mailer)
        ->template('testView', ['name' => 'Template body'])
        ->getBody();

    expect($body)->toBe('<div>Template body</div>');
});

it('renders multiple mail rows and keeps row methods chainable', function (): void {
    $compiledPath = sys_get_temp_dir().'/humble-core-tests/'.getmypid().'/views';

    if (! is_dir($compiledPath)) {
        mkdir($compiledPath, 0777, true);
    }

    $this->bootApplication([ViewServiceProvider::class], [
        'mail' => [
            'mailer' => 'log',
            'from' => [
                'name' => 'Humble Core',
                'address' => 'test@example.test',
            ],
        ],
        'view' => [
            'paths' => [dirname(__DIR__).'/tests/resources/views'],
            'compiled' => $compiledPath,
        ],
    ]);

    $message = new MailMessage;

    expect($message->heading('Welcome'))->toBe($message)
        ->and($message->text('Thanks for joining'))->toBe($message)
        ->and($message->button('Open dashboard', 'https://example.test/dashboard'))->toBe($message)
        ->and($message->rows)->toBe([
            ['type' => 'heading', 'value' => 'Welcome'],
            ['type' => 'content', 'value' => 'Thanks for joining'],
            ['type' => 'button', 'label' => 'Open dashboard', 'url' => 'https://example.test/dashboard'],
        ])
        ->and($message->getBody())->toContain('Welcome', 'Thanks for joining');
});
