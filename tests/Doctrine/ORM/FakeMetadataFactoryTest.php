<?php declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ReflectionService;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Solido\TestUtils\Doctrine\ORM\FakeMetadataFactory;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Tests\fixtures\Doctrine\ORM\TestEntity;

class FakeMetadataFactoryTest extends TestCase
{
    use ProphecyTrait;

    private FakeMetadataFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new FakeMetadataFactory();
    }

    public function testShouldInitializeReflection(): void
    {
        $metadata = $this->prophesize(ClassMetadata::class);
        $metadata->initializeReflection(Argument::type(ReflectionService::class))->shouldBeCalled();
        $metadata->wakeupReflection(Argument::type(ReflectionService::class))->shouldBeCalled();

        $this->factory->setMetadataFor(TestEntity::class, $metadata->reveal());
    }
}
