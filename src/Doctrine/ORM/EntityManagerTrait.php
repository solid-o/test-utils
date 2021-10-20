<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Cache\Adapter\PHPArray\ArrayCachePool;
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
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Refugis\DoctrineExtra\ORM\EntityRepository;
use Solido\TestUtils\Prophecy\Argument\Token\StringMatchesToken;

use function array_values;
use function class_exists;
use function interface_exists;
use function method_exists;
use function preg_quote;
use function sys_get_temp_dir;

use const CASE_LOWER;

trait EntityManagerTrait
{
    use ProphecyTrait;

    private ?EntityManagerInterface $_entityManager = null;
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

            $this->_innerConnection = $this->prophesize(interface_exists(ServerInfoAwareConnection::class) ? ServerInfoAwareConnection::class : PDOConnection::class);

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

            $this->queryLike('SELECT DATABASE()', [], [
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

    /**
     * @param array<string|int, mixed> $parameters
     * @param array<array<string, string>> $results
     */
    private function queryLike(string $query, array $parameters = [], array $results = []): void
    {
        $this->queryMatches('/' . preg_quote($query, '/') . '/', $parameters, $results);
    }

    /**
     * @param array<string|int, mixed> $parameters
     * @param array<array<string, string>> $results
     */
    private function queryMatches(string $query, array $parameters = [], array $results = []): void
    {
        if (method_exists(Statement::class, 'setFetchMode')) {
            $this->_innerConnection->{$parameters ? 'prepare' : 'query'}(new StringMatchesToken($query))
                ->willReturn($stmt = $this->prophesize(Statement::class));

            foreach (array_values($parameters) as $key => $value) {
                $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
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

            foreach (array_values($parameters) as $key => $value) {
                $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
            }

            $stmt->execute()->willReturn(new DummyResult($results));
        }
    }

    /**
     * @param array<string|int, mixed> $parameters
     */
    private function executeLike(string $query, array $parameters = [], int $rowCount = 0): void
    {
        $this->_innerConnection->prepare(Argument::containingString($query))
            ->willReturn($stmt = $this->prophesize(Statement::class));

        foreach (array_values($parameters) as $key => $value) {
            $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
        }

        if (method_exists(Statement::class, 'setFetchMode')) {
            $stmt->execute()->willReturn();
            $stmt->rowCount()->willReturn($rowCount);
        } else {
            $stmt->execute()->willReturn(new DummyResult([]));
        }
    }
}
