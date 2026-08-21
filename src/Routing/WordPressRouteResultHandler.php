<?php

namespace HumbleCore\Routing;

use Illuminate\Contracts\Support\Renderable;
use Stringable;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class WordPressRouteResultHandler
{
    public function send(mixed $result): void
    {
        if ($result instanceof Response) {
            $result->send();

            return;
        }

        if ($result instanceof Renderable) {
            echo $result->render();

            return;
        }

        if (is_string($result) || $result instanceof Stringable) {
            echo (string) $result;

            return;
        }

        throw new UnexpectedValueException('WordPress routes must return a response, renderable, or string.');
    }
}
