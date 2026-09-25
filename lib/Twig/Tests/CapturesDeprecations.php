<?php declare(strict_types=1);

namespace Pagerfanta\Twig\Tests;

trait CapturesDeprecations
{
    /**
     * Runs the callback and returns the messages of the deprecations it triggered.
     *
     * @param callable(): mixed $callback
     *
     * @return list<string>
     */
    private function captureDeprecations(callable $callback): array
    {
        $deprecations = [];

        set_error_handler(
            static function (int $level, string $message) use (&$deprecations): bool {
                $deprecations[] = $message;

                return true;
            },
            \E_USER_DEPRECATED
        );

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $deprecations;
    }
}
