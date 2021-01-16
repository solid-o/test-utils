<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Proxy\ProxyFactory;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Refugis\DoctrineExtra\ORM\EntityRepository;

use function array_values;
use function sys_get_temp_dir;

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

            $this->_configuration->setResultCacheImpl(new ArrayCache());
            $this->_configuration->setClassMetadataFactoryName(FakeMetadataFactory::class);
            $this->_configuration->setMetadataDriverImpl($this->prophesize(MappingDriver::class)->reveal());
            $this->_configuration->setProxyDir(sys_get_temp_dir());
            $this->_configuration->setProxyNamespace('__TMP__\\ProxyNamespace\\');
            $this->_configuration->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_ALWAYS);
            $this->_configuration->setDefaultRepositoryClassName(EntityRepository::class);
            $this->_configuration->setRepositoryFactory(new TestRepositoryFactory());
            $this->_configuration->setNamingStrategy(new UnderscoreNamingStrategy());

            $this->_innerConnection = $this->prophesize(PDOConnection::class);

            $this->_connection = new Connection([
                'pdo' => $this->_innerConnection->reveal(),
                'platform' => $this->getConnectionPlatform(),
            ], new Driver(), $this->_configuration);

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
    }

    /**
     * @param array<string|int, mixed> $parameters
     * @param array<array<string, string>> $results
     */
    private function queryLike(string $query, array $parameters = [], array $results = []): void
    {
        $this->_innerConnection->{$parameters ? 'prepare' : 'query'}(Argument::containingString($query))
            ->willReturn($stmt = $this->prophesize(Statement::class));

        foreach (array_values($parameters) as $key => $value) {
            $stmt->bindValue($key + 1, $value, Argument::any())->willReturn();
        }

        $stmt->execute()->willReturn();
        $stmt->setFetchMode(FetchMode::ASSOCIATIVE)->willReturn();
        $stmt->closeCursor()->willReturn();

        $stmt->fetchAll(FetchMode::ASSOCIATIVE)->willReturn($results);
        $stmt->fetchAll()->willReturn($results);

        $results[] = null;
        $stmt->fetch(FetchMode::ASSOCIATIVE)->willReturn(...$results);
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

        $stmt->execute()->willReturn();

        $results[] = null;
        $stmt->rowCount()->willReturn($rowCount);
    }
}
