<?php

use HumbleCore\App\Application;

if (! function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): Application
    {
        if (is_null($abstract)) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($abstract, $parameters);
    }
}