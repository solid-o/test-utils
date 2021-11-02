<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Laravel;

use Solido\TestUtils\HttpTestCaseInterface;
use Solido\TestUtils\Laravel\FunctionalTestTrait;
use Solido\TestUtils\Laravel\WebTestCase;
use Solido\TestUtils\Tests\fixtures\Laravel\TestKernel;

class WebFunctionalTestTraitTest extends WebTestCase implements HttpTestCaseInterface
{
    use FunctionalTestTrait;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function getApplicationBasePath(): string
    {
        return TestKernel::getBasePath();
    }

    public function testBootShutdown(): void
    {
        self::get('/');
        self::assertEquals(1, TestKernel::$bootCount);
        self::assertEquals(1, TestKernel::$shutdownCount);

        self::ensureKernelShutdown();
        self::ensureKernelShutdown();
        self::ensureKernelShutdown();
        self::assertEquals(1, TestKernel::$shutdownCount);

        self::get('/');
        self::assertEquals(2, TestKernel::$bootCount);
        self::assertEquals(2, TestKernel::$shutdownCount);
    }
}
