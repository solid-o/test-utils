<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Error;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Refugis\DoctrineExtra\ORM\EntityRepository;
use RuntimeException;
use Solido\TestUtils\Doctrine\ORM\EntityManagerTrait;
use Solido\TestUtils\Doctrine\ORM\FakeMetadataFactory;
use Solido\TestUtils\Doctrine\ORM\MockPlatform;

use Solido\TestUtils\Tests\fixtures\Doctrine\ORM\TestEntity;
use function spl_object_hash;

class EntityManagerTraitTest extends TestCase
{
    use ProphecyTrait;

    private EntityManagerTestConcrete $obj;

    protected function setUp(): void
    {
        $this->obj = new EntityManagerTestConcrete();
    }

    public function testGetEntityManagerShouldReturnAnEntityManager(): void
    {
        $em = $this->obj->getEntityManager();
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        self::assertInstanceOf(FakeMetadataFactory::class, $em->getMetadataFactory());

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
        self::assertEquals([['x1' => 'foo']], method_exists($stmt, 'fetchAll') ? $stmt->fetchAll() : $stmt->fetchAllAssociative());
    }

    public function testQueryLikeShouldQuoteQueryToBeExecutedCorrectly(): void
    {
        $this->obj->getEntityManager();
        $this->obj->queryLike('WHERE y LIKE \'%/path%\'', [], [['x1' => 'bar']]);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->query('SELECT * FROM x WHERE y LIKE \'%/path%\'');
        self::assertEquals([['x1' => 'bar']], method_exists($stmt, 'fetchAll') ? $stmt->fetchAll() : $stmt->fetchAllAssociative());
    }

    public function testQueryMatchesShouldRegisterQueryToBeExecuted(): void
    {
        $this->obj->getEntityManager();
        $this->obj->queryMatches('/FROM xy?z/i', [], [['x1' => 'foo']]);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->query('SELECT * from xz WHERE x.id = 1');
        self::assertEquals([['x1' => 'foo']], method_exists($stmt, 'fetchAll') ? $stmt->fetchAll() : $stmt->fetchAllAssociative());

        $this->obj->queryMatches('/FROM xy?z/i', ['param' => 42], [['x1' => 'foo']]);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->prepare('SELECT * from xz WHERE x.id = ?');
        $stmt->bindValue(1, 42);
        $stmt = $stmt->execute();

        self::assertEquals([['x1' => 'foo']], method_exists($stmt, 'fetchAll') ? $stmt->fetchAll() : $stmt->fetchAllAssociative());
    }

    public function testExecuteMatchesShouldRegisterQueryToBeExecuted(): void
    {
        $this->obj->getEntityManager();
        $this->obj->executeLike('INTO xz SET', [], 0);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->prepare('INSERT INTO xz SET id = 1');
        $stmt = $stmt->execute();
        self::assertEquals(0, $stmt->rowCount());

        $this->obj->executeMatches('/INTO xy?z VALUES/i', ['param' => 42]);

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->prepare('INSERT INTO xz VALUES (?)');
        $stmt->bindValue(1, 42);
        $stmt = $stmt->execute();
        self::assertEquals(1, $stmt->rowCount());
    }

    public function testExecuteLikeShouldQuoteQueryCorrectly(): void
    {
        $this->obj->getEntityManager();
        $this->obj->executeLike('INTO xz VALUES (\'/\')');

        $connection = $this->obj->getInnerConnection()->reveal();
        $stmt = $connection->prepare('INSERT INTO xz VALUES (\'/\')');
        $stmt = $stmt->execute();
        self::assertEquals(1, $stmt->rowCount());
    }

    public function testLoadEntityMetadata(): void
    {
        $this->obj->loadEntityMetadata(TestEntity::class);

        $em = $this->obj->getEntityManager();
        $metadata = $em->getClassMetadata(TestEntity::class);

        self::assertInstanceOf(ClassMetadata::class, $metadata);
    }

    public function testShouldThrowOnLoadEntityMetadataForNonExistentClass(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot load entity metadata for class "Solido\TestUtils\Tests\Doctrine\ORM\NonExistent": Class "Solido\TestUtils\Tests\Doctrine\ORM\NonExistent" does not exist');

        $this->obj->loadEntityMetadata(NonExistent::class);
    }

    public function testShouldThrowOnNonGuessableDriver(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot guess metadata driver for class "Solido\TestUtils\Tests\Doctrine\ORM\EntityManagerTraitTest"');

        $this->obj->loadEntityMetadata(self::class);
    }

    public function testLoadEntityMetadataNamingStratefy(): void
    {
        $this->obj->loadEntityMetadata(TestEntity::class);

        $em = $this->obj->getEntityManager();
        $metadata = $em->getClassMetadata(TestEntity::class);

        $mapping = $metadata->getFieldMapping('field42Name');
        self::assertEquals('field42_name', $mapping['columnName']);
    }

    public function testGetRepository(): void
    {
        $this->obj->loadEntityMetadata(TestEntity::class);

        $em = $this->obj->getEntityManager();
        $repository = $em->getRepository(TestEntity::class);

        self::assertInstanceOf(EntityRepository::class, $repository);
    }

    public function testSetRepository(): void
    {
        $this->obj->loadEntityMetadata(TestEntity::class);
        $this->obj->setRepository(TestEntity::class, $repo = $this->prophesize(EntityRepository::class));

        $repo->get('12')->willReturn(new TestEntity());

        $em = $this->obj->getEntityManager();
        $repository = $em->getRepository(TestEntity::class);

        self::assertInstanceOf(EntityRepository::class, $repository);
        self::assertInstanceOf(TestEntity::class, $repository->get('12'));
    }

    public function testGetCurrentDatabase(): void
    {
        $this->obj->loadEntityMetadata(TestEntity::class);

        $em = $this->obj->getEntityManager();
        $connection = $em->getConnection();

        $database = $connection->getDatabase();
        self::assertEquals('database', $database);
    }
}

class EntityManagerTestConcrete extends TestCase
{
    use EntityManagerTrait {
        getConnectionPlatform as public;
        loadEntityMetadata as public;
        queryLike as public;
        queryMatches as public;
        executeLike as public;
        executeMatches as public;
        setRepository as public;
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
