<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Solido\TestUtils\Symfony\FunctionalTestTrait as SymfonyFunctionalTestTrait;

trait FunctionalTestTrait
{
    use SymfonyFunctionalTestTrait;

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected static function ensureKernelShutdown(): void
    {
        static::$booted = false;
    }

    protected static function enableProfiler(): void
    {
        // Do nothing
    }
}
