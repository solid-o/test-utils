<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as MySQLDriver;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecyInterface;
use ReflectionClass;
use ReflectionException;
use Refugis\DoctrineExtra\ORM\EntityRepository;
use RuntimeException;
use Solido\TestUtils\Doctrine\ORM\Driver as DoctrineDriver;
use Solido\TestUtils\Prophecy\Argument\Token\StringMatchesToken;

use function array_merge;
use function array_values;
use function assert;
use function class_exists;
use function count;
use function dirname;
use function interface_exists;
use function method_exists;
use function preg_quote;
use function sprintf;
use function sys_get_temp_dir;

use const CASE_LOWER;
use const PHP_VERSION_ID;

trait EntityManagerTrait
{
    use ProphecyTrait;

    private EntityManagerInterface|null $_entityManager = null;
    private Connection $_connection;
    private Configuration $_configuration;

    /** @var DriverConnection|ObjectProphecy */
    private ObjectProphecy $_innerConnection;

    public function getEntityManager(): EntityManager
    {
        if ($this->_entityManager === null) {
            $this->_configuration = new Configuration();

            if (class_exists(DoctrineProvider::class)) {
                $this->_configuration->setResultCacheImpl(DoctrineProvider::wrap(new ArrayCachePool()));
            } elseif (class_exists(ArrayCache::class)) {
                $this->_configuration->setResultCacheImpl(new ArrayCache());
            }

            $this->_configuration->setClassMetadataFactoryName(FakeMetadataFactory::class);
            $this->_configuration->setMetadataDriverImpl($this->prophesize(MappingDriver::class)->reveal());
            $this->_configuration->setProxyDir(sys_get_temp_dir());
            $this->_configuration->setProxyNamespace('__TMP__\\ProxyNamespace\\');
            $this->_configuration->setAutoGenerateProxyClasses(class_exists(AbstractProxyFactory::class) ? AbstractProxyFactory::AUTOGENERATE_ALWAYS : ProxyFactory::AUTOGENERATE_ALWAYS);
            $this->_configuration->setDefaultRepositoryClassName(EntityRepository::class);
            $this->_configuration->setRepositoryFactory(new TestRepositoryFactory());
            $this->_configuration->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER, true));

            $this->_innerConnection = $this->prophesize(interface_exists(DriverConnection::class) ? DriverConnection::class : PDOConnection::class);
            if (interface_exists(ServerInfoAwareConnection::class)) {
                $this->_innerConnection->willImplement(ServerInfoAwareConnection::class);
            }

            if (interface_exists(ServerInfoAwareConnection::class)) {
                $this->_connection = new Connection([
                    'user' => 'user',
                    'name' => 'dbname',
                    'platform' => $this->getConnectionPlatform(),
                ], class_exists(MySQLDriver::class) ? new MySQLDriver() : new Driver(), $this->_configuration);

                (fn (ServerInfoAwareConnection $connection) => $this->_conn = $connection)
                    ->bindTo($this->_connection, Connection::class)($this->_innerConnection->reveal());
            } else {
                $this->_connection = new Connection([
                    'pdo' => $this->_innerConnection->reveal(),
                    'platform' => $this->getConnectionPlatform(),
                ], class_exists(MySQLDriver::class) ? new MySQLDriver() : new Driver(), $this->_configuration);
            }

            $this->_entityManager = EntityManager::create($this->_connection, $this->_configuration);
            $this->onEntityManagerCreated();

            $platform = $this->_connection->getDatabasePlatform();
            assert($platform !== null);

            $this->queryLike('SELECT ' . $platform->getCurrentDatabaseExpression(), [], [
                ['database'],
            ]);
        }

        return $this->_entityManager;
    }

    private function getConnectionPlatform(): AbstractPlatform
    {
        return new MockPlatform();
    }

    private function onEntityManagerCreated(): void
    {
        // Intentionally empty.
    }

    /** @param string[]|null $paths */
    private function loadEntityMetadata(string $className, string|null $driver = null, array|null $paths = null): void
    {
        try {
            $reflectionClass = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new RuntimeException(sprintf('Cannot load entity metadata for class "%s": %s', $className, $e->getMessage()));
        }

        if ($paths === null) {
            $paths = [];
            $fileName = $reflectionClass->getFileName();
            if ($fileName !== false) {
                $paths[] = dirname($fileName);
            }
        }

        $mappingDriver = $driver === null
            ? $this->guessMetadataDriver($reflectionClass, $paths)
            : DoctrineDriver::createDriver($driver, $paths);

        $entityManager = $this->getEntityManager();
        $configuration = $entityManager->getConfiguration();

        $metadata = new ClassMetadata($className, $configuration->getNamingStrategy());
        $metadata->reflClass = $reflectionClass;
        $mappingDriver->loadMetadataForClass($className, $metadata);

        $entityManager->getMetadataFactory()->setMetadataFor($className, $metadata);
    }

    /** @param string[] $paths */
    private function guessMetadataDriver(ReflectionClass $reflectionClass, array $paths): MappingDriver
    {
        if (PHP_VERSION_ID >= 80000) {
            $attributes = array_merge(
                $reflectionClass->getAttributes(Entity::class),
                $reflectionClass->getAttributes(MappedSuperclass::class),
                $reflectionClass->getAttributes(Embeddable::class),
            );

            if (count($attributes) > 0) {
                return DoctrineDriver::createDriver(DoctrineDriver::ATTRIBUTE, $paths);
            }
        }

        $reader = new AnnotationReader();
        $annot = $reader->getClassAnnotation($reflectionClass, Entity::class) ??
            $reader->getClassAnnotation($reflectionClass, MappedSuperclass::class) ??
            $reader->getClassAnnotation($reflectionClass, Embeddable::class);
        if ($annot !== null) {
            return DoctrineDriver::createDriver(DoctrineDriver::ANNOTATION, $paths);
        }

        throw new RuntimeException(sprintf('Cannot guess metadata driver for class "%s"', $reflectionClass->name));
    }

    /** @param ProphecyInterface|ObjectRepository $repository */
    private function setRepository(string $entityName, object $repository): void
    {
        $em = $this->getEntityManager();
        $configuration = $em->getConfiguration();
        $repositoryFactory = $configuration->getRepositoryFactory();

        assert($repositoryFactory instanceof TestRepositoryFactory);

        $repositoryFactory->setRepository($em, $entityName, $repository);
    }

    /**
     * @param array<string|int, mixed> $parameters
     * @param array<array<string, string>> $results
     */
    private function queryLike(string $query, array $parameters = [], array $results = []): void
    {
        $this->queryMatches('/' . preg_quote($query, '/') . '/', $parameters, $results);
    }

    /** @param array<string|int, mixed> $parameters */
    private function executeLike(string $query, array $parameters = [], int $rowCount = 1): void
    {
        $this->executeMatches('/' . preg_quote($query, '/') . '/', $parameters, $rowCount);
    }

    /**
     * @param array<string|int, mixed> $parameters
     * @param array<array<string, string>> $results
     */
    private function queryMatches(string $query, array $parameters = [], array $results = []): void
    {
        /* @infection-ignore-all */
        if (method_exists(Statement::class, 'setFetchMode')) {
            $this->_innerConnection->{$parameters ? 'prepare' : 'query'}(new StringMatchesToken($query))
                ->willReturn($stmt = $this->prophesize(Statement::class));

            /* @infection-ignore-all */
            if (empty($parameters)) {
                $stmt->bindValue(Argument::cetera())->willReturn();
            } else {
                foreach (array_values($parameters) as $key => $value) {
                    $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
                }
            }

            $stmt->execute()->willReturn();
            $stmt->setFetchMode(FetchMode::ASSOCIATIVE, Argument::cetera())->willReturn();
            $stmt->closeCursor()->willReturn();

            $stmt->fetchAll(FetchMode::ASSOCIATIVE, Argument::cetera())->willReturn($results);
            $stmt->fetchAll()->willReturn($results);

            $results[] = null;
            $stmt->fetch(FetchMode::ASSOCIATIVE, Argument::cetera())->willReturn(...$results);

            if (! method_exists(Statement::class, 'fetchAssociative')) {
                return;
            }

            $stmt->fetchAssociative()->willReturn(...$results);
        } elseif (empty($parameters)) {
            $this->_innerConnection->query(new StringMatchesToken($query))
                ->willReturn(new DummyResult($results));
        } else {
            $this->_innerConnection->prepare(new StringMatchesToken($query))
                ->willReturn($stmt = $this->prophesize(Statement::class));

            /* @infection-ignore-all */
            if (empty($parameters)) {
                $stmt->bindValue(Argument::cetera())->willReturn();
            } else {
                foreach (array_values($parameters) as $key => $value) {
                    $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
                }
            }

            $stmt->execute()->willReturn(new DummyResult($results));
        }
    }

    /** @param array<string|int, mixed> $parameters */
    private function executeMatches(string $query, array $parameters = [], int $rowCount = 1): void
    {
        $this->_innerConnection->prepare(new StringMatchesToken($query))
            ->willReturn($stmt = $this->prophesize(Statement::class));

        /* @infection-ignore-all */
        if (empty($parameters)) {
            $stmt->bindValue(Argument::cetera())->willReturn();
        } else {
            foreach (array_values($parameters) as $key => $value) {
                $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
            }
        }

        /* @infection-ignore-all */
        if (method_exists(Statement::class, 'setFetchMode')) {
            $stmt->execute()->willReturn();
            $stmt->rowCount()->willReturn($rowCount);
        } else {
            $stmt->execute()->willReturn(new DummyResult([], $rowCount));
        }
    }
}
