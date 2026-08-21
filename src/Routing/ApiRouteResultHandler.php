<?php

namespace HumbleCore\Routing;

use Symfony\Component\HttpFoundation\Response;

class ApiRouteResultHandler
{
    public function toResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        return response($result, 200, [
            'Cache-Control' => 'public',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
