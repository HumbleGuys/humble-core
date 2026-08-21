<?php

declare(strict_types=1);

namespace Tests\Support {
    final class WordPressFunctions
    {
        /** @var list<array<string, mixed>> */
        public static array $getPostsCalls = [];

        public static bool $isUserLoggedIn = false;

        /** @var array<int, object> */
        public static array $posts = [];

        public static function reset(): void
        {
            self::$getPostsCalls = [];
            self::$isUserLoggedIn = false;
            self::$posts = [];
        }
    }
}

namespace {
    use Tests\Support\WordPressFunctions;

    if (! function_exists('get_posts')) {
        /** @return array<int, object> */
        function get_posts(mixed $args = null): array
        {
            WordPressFunctions::$getPostsCalls[] = (array) $args;

            return WordPressFunctions::$posts;
        }
    }

    if (! function_exists('is_user_logged_in')) {
        function is_user_logged_in(): bool
        {
            return WordPressFunctions::$isUserLoggedIn;
        }
    }
}
