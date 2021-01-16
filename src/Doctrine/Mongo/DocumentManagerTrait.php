<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\Mongo;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\SchemaManager;
use MongoClient;
use MongoCollection;
use MongoDB;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\TestUtils\Doctrine\ORM\FakeMetadataFactory;

use function assert;
use function sys_get_temp_dir;

trait DocumentManagerTrait
{
    use ProphecyTrait;

    private DocumentManager $documentManager;

    /** @var Client|ObjectProphecy */
    private ObjectProphecy $client;

    /** @var Database|ObjectProphecy */
    private ObjectProphecy $database;

    /** @var Collection|ObjectProphecy */
    private ObjectProphecy $collection;

    private Connection $connection;
    private Configuration $configuration;

    public function getDocumentManager(): DocumentManager
    {
        if ($this->documentManager === null) {
            $mongoDb = null;

            $server = $this->prophesize(MongoClient::class);
            assert($server instanceof MongoClient || $server instanceof ObjectProphecy);
            $server->getReadPreference()->willReturn(['type' => MongoClient::RP_PRIMARY]);
            $server->getWriteConcern()->willReturn([
                'w' => 1,
                'wtimeout' => 5000,
            ]);
            $server->selectDB('doctrine')->will(function ($args) use (&$mongoDb) {
                [$dbName] = $args;
                if (isset($mongoDb)) {
                    return $mongoDb;
                }

                return $mongoDb = new MongoDB($this->reveal(), $dbName);
            });

            $this->client = $this->prophesize(Client::class);
            $server->getClient()->willReturn($this->client);

            $this->client->selectDatabase('doctrine', Argument::any())
                ->willReturn($this->database = $this->prophesize(Database::class));
            $this->client->selectCollection('doctrine', 'FooBar', Argument::any())
                ->willReturn($this->collection = $this->prophesize(Collection::class));
            $this->database->selectCollection('FooBar', Argument::any())->willReturn($this->collection);

            $server->selectCollection(Argument::cetera())->willReturn($oldCollection = $this->prophesize(MongoCollection::class));
            $oldCollection->getCollection()->willReturn($this->collection);

            $schemaManager = $this->prophesize(SchemaManager::class);
            $metadataFactory = new FakeMetadataFactory();
            $this->connection = new Connection($server->reveal());

            $this->configuration = new Configuration();
            $this->configuration->setHydratorDir(sys_get_temp_dir());
            $this->configuration->setHydratorNamespace('__TMP__\\HydratorNamespace');
            $this->configuration->setProxyDir(sys_get_temp_dir());
            $this->configuration->setProxyNamespace('__TMP__\\ProxyNamespace');

            $this->documentManager = DocumentManager::create($this->connection, $this->configuration);

            (function () use ($schemaManager) {
                $this->schemaManager = $schemaManager->reveal();
            })->call($this->documentManager);

            (function () use ($metadataFactory) {
                $this->metadataFactory = $metadataFactory;
            })->call($this->documentManager);
        }

        return $this->documentManager;
    }
}
