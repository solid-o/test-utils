<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Solido\TestUtils\Doctrine\ORM\TestRepositoryFactory;
use Solido\TestUtils\Tests\fixtures\Doctrine\ORM\TestEntity;
use stdClass;
use TypeError;

class TestRepositoryFactoryTest extends TestCase
{
    use ProphecyTrait;

    private TestRepositoryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new TestRepositoryFactory();
    }

    public function testShouldRevealTheRepository(): void
    {
        $repository = $this->prophesize(EntityRepository::class);
        $em = $this->prophesize(EntityManagerInterface::class);
        $em->getClassMetadata(TestEntity::class)
            ->willReturn($metadata = $this->prophesize(ClassMetadata::class));
        $metadata->getName()->willReturn(TestEntity::class);

        $this->factory->setRepository($em->reveal(), TestEntity::class, $repository);
        $r = $this->factory->getRepository($em->reveal(), TestEntity::class);

        self::assertSame($r, $repository->reveal());
    }

    public function testShouldThrowOnInvalidObject(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument 3 passed to Solido\TestUtils\Doctrine\ORM\TestRepositoryFactory::setRepository must implement interface ' . ObjectRepository::class . ', instance of stdClass given.');

        $em = $this->prophesize(EntityManagerInterface::class);
        $this->factory->setRepository($em->reveal(), TestEntity::class, new stdClass());
    }
}
