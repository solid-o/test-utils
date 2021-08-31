<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Error;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\TestUtils\Doctrine\ORM\EntityManagerTrait;
use Solido\TestUtils\Doctrine\ORM\MockPlatform;

use function spl_object_hash;

class EntityManagerTraitTest extends TestCase
{
    private EntityManagerTestConcrete $obj;

    protected function setUp(): void
    {
        $this->obj = new EntityManagerTestConcrete();
    }

    public function testGetEntityManagerShouldReturnAnEntityManager(): void
    {
        $em = $this->obj->getEntityManager();
        self::assertInstanceOf(EntityManagerInterface::class, $em);

        $newEm = $this->obj->getEntityManager();
        self::assertSame(spl_object_hash($em), spl_object_hash($newEm));
    }

    public function testConnectionPlatformIsAMock(): void
    {
        self::assertInstanceOf(MockPlatform::class, $this->obj->getConnectionPlatform());
    }

    public function testInnerConnectionIsUninitialized(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('must not be accessed before initialization');
        $this->obj->getInnerConnection();
    }

    public function testInnerConnectionIsInitializedAfterGetEntityManagerCall(): void
    {
        $this->obj->getEntityManager();
        $connection = $this->obj->getInnerConnection();

        self::assertInstanceOf(ObjectProphecy::class, $connection);
    }

    public function testOnEntityMangerCreatedIsCalledOnGetEntityManager(): void
    {
        self::assertEquals(0, $this->obj->createdCall);
        $this->obj->getEntityManager();
        self::assertEquals(1, $this->obj->createdCall);
    }

    public function testQueryLikeShouldRegisterQueryToBeExecuted(): void
    {
        $this->obj->getEntityManager();
        $this->obj->queryLike('FROM x', [], [['x1' => 'foo']]);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->query('SELECT * FROM x WHERE x.id = 1');
        self::assertEquals([['x1' => 'foo']], $stmt->fetchAll());
    }

    public function testQueryMatchesShouldRegisterQueryToBeExecuted(): void
    {
        $this->obj->getEntityManager();
        $this->obj->queryMatches('/FROM xy?z/i', [], [['x1' => 'foo']]);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->query('SELECT * from xz WHERE x.id = 1');
        self::assertEquals([['x1' => 'foo']], $stmt->fetchAll());
    }
}

class EntityManagerTestConcrete extends TestCase
{
    use EntityManagerTrait {
        getConnectionPlatform as public;
        queryLike as public;
        queryMatches as public;
    }

    public int $createdCall = 0;

    public function getInnerConnection()
    {
        return $this->_innerConnection;
    }

    private function onEntityManagerCreated(): void
    {
        $this->createdCall++;
    }
}
