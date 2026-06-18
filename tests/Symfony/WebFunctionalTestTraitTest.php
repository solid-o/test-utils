<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Symfony;

use Solido\TestUtils\HttpTestCaseInterface;
use Solido\TestUtils\Symfony\FunctionalTestTrait;
use Solido\TestUtils\Tests\fixtures\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebFunctionalTestTraitTest extends WebTestCase implements HttpTestCaseInterface
{
    use FunctionalTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        TestKernel::$bootCount = 0;
        TestKernel::$shutdownCount = 0;
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testBootShutdown(): void
    {
        $kernel = self::bootKernel();
        self::assertTrue($kernel->isBooted());

        $bootCount = TestKernel::$bootCount;
        $shutdownCount = TestKernel::$shutdownCount;
        self::assertGreaterThanOrEqual(1, $bootCount);

        self::ensureKernelShutdown();
        self::assertFalse($kernel->isBooted());
        self::assertEquals($bootCount, TestKernel::$bootCount);
        self::assertEquals($shutdownCount + 1, TestKernel::$shutdownCount);

        self::get('/');
        self::assertGreaterThan($bootCount, TestKernel::$bootCount);

        $bootCount = TestKernel::$bootCount;
        $shutdownCount = TestKernel::$shutdownCount;

        self::ensureKernelShutdown();
        self::ensureKernelShutdown();
        self::ensureKernelShutdown();
        self::assertEquals($bootCount, TestKernel::$bootCount);
        self::assertEquals($shutdownCount + 1, TestKernel::$shutdownCount);

        self::get('/');
        self::assertGreaterThan($bootCount, TestKernel::$bootCount);
    }
}
