<?php

namespace HumbleCore\Routing;

use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class ApiRouteResultHandler
{
    public function toResponse(mixed $result): Response
    {
        if ($result === null) {
            throw new UnexpectedValueException('API route actions must return a response value.');
        }

        if ($result instanceof Response) {
            if (! $result->headers->has('Access-Control-Allow-Origin')) {
                $result->headers->set('Access-Control-Allow-Origin', '*');
            }

            if (! $result->headers->has('Cache-Control')) {
                $result->headers->set('Cache-Control', 'public');
            }

            return $result;
        }

        return response($result, 200, [
            'Cache-Control' => 'public',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
