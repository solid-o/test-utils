<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Solido\TestUtils\Symfony\FunctionalTestTrait as SymfonyFunctionalTestTrait;

use function restore_error_handler;
use function restore_exception_handler;

trait FunctionalTestTrait
{
    use SymfonyFunctionalTestTrait;

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected static function ensureKernelShutdown(): void
    {
        if (static::$booted) {
            restore_error_handler();
            restore_exception_handler();
        }

        static::$booted = false;
    }

    protected static function enableProfiler(): void
    {
        // Do nothing
    }
}
