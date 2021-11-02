<?php

declare(strict_types=1);

namespace Solido\TestUtils\Laravel;

use Solido\TestUtils\Symfony\FunctionalTestTrait as SymfonyFunctionalTestTrait;

trait FunctionalTestTrait
{
    use SymfonyFunctionalTestTrait;

    protected static function enableProfiler(): void
    {
        // Do nothing
    }
}
