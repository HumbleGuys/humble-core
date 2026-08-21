<?php

declare(strict_types=1);

use HumbleCore\Routing\Route;

it('rejects an unknown WordPress route type with a routing exception', function (): void {
    (new Route('WP', 'unknown', static fn (): null => null))->url(null);
})->throws(UnexpectedValueException::class, 'Unknown route type.');
