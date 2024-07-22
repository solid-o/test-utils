<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Solido\TestUtils\Doctrine\ORM\Driver;

class DriverTest extends TestCase
{
    public function testShouldCreateAnnotationDriver(): void
    {
        $driver = Driver::createDriver(Driver::ANNOTATION, [__DIR__]);
        self::assertInstanceOf(AnnotationDriver::class, $driver);
    }

    #[RequiresPhp('>= 8.0')]
    public function testShouldCreateAttributeDriver(): void
    {
        $driver = Driver::createDriver(Driver::ATTRIBUTE, [__DIR__]);
        self::assertInstanceOf(AttributeDriver::class, $driver);
    }

    public function testShouldThrowOnUnsupportedDriver(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('"unsupported" driver is not supported by Solido\TestUtils\Doctrine\ORM\Driver::createDriver. Currently supported drivers are "attribute", "annotation"');

        Driver::createDriver('unsupported', []);
    }
}
