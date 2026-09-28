<?php

declare(strict_types=1);

namespace Tests\Support {
    final class WordPressRoutingFunctions
    {
        /** @var list<int> */
        public static array $statusCodes = [];

        public static bool $noCacheHeadersSent = false;

        public static function reset(): void
        {
            self::$statusCodes = [];
            self::$noCacheHeadersSent = false;
        }
    }
}

namespace HumbleCore\Routing {
    use Tests\Support\WordPressRoutingFunctions;

    function status_header(int $code): void
    {
        WordPressRoutingFunctions::$statusCodes[] = $code;
    }

    function nocache_headers(): void
    {
        WordPressRoutingFunctions::$noCacheHeadersSent = true;
    }
}
