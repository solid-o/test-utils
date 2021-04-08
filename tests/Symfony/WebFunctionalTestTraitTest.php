<?php declare(strict_types=1);

namespace Solido\TestUtils\Tests\Symfony;

use Solido\TestUtils\Symfony\FunctionalTestTrait;
use Solido\TestUtils\Tests\fixtures\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebFunctionalTestTraitTest extends WebTestCase
{
    use FunctionalTestTrait;

    protected static function getKernelClass()
    {
        return TestKernel::class;
    }

    public function testBootShutdown(): void
    {
        $kernel = self::bootKernel();
        self::assertTrue($kernel->isBooted());
        self::assertEquals(1, TestKernel::$bootCount);

        self::ensureKernelShutdown();
        self::assertFalse($kernel->isBooted());
        self::assertEquals(1, TestKernel::$shutdownCount);

        self::get('/');
        self::assertEquals(2, TestKernel::$bootCount);
        self::assertEquals(1, TestKernel::$shutdownCount);

        self::ensureKernelShutdown();
        self::ensureKernelShutdown();
        self::ensureKernelShutdown();
        self::assertEquals(2, TestKernel::$shutdownCount);

        self::get('/');
        self::assertEquals(3, TestKernel::$bootCount);
        self::assertEquals(2, TestKernel::$shutdownCount);
    }
}
