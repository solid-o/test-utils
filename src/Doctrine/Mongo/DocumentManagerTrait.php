<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\Mongo;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\SchemaManager;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Solido\TestUtils\Doctrine\ORM\FakeMetadataFactory;

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

    private Configuration $configuration;

    public function getDocumentManager(): DocumentManager
    {
        if ($this->documentManager === null) {
            $mongoDb = null;

            $this->client = $this->prophesize(Client::class);

            $this->client->selectDatabase('doctrine', Argument::any())
                ->willReturn($this->database = $this->prophesize(Database::class));
            $this->client->selectCollection('doctrine', 'FooBar', Argument::any())
                ->willReturn($this->collection = $this->prophesize(Collection::class));
            $this->database->selectCollection('FooBar', Argument::any())->willReturn($this->collection);

            $schemaManager = $this->prophesize(SchemaManager::class);
            $metadataFactory = new FakeMetadataFactory();

            $this->configuration = new Configuration();
            $this->configuration->setHydratorDir(sys_get_temp_dir());
            $this->configuration->setHydratorNamespace('__TMP__\\HydratorNamespace');
            $this->configuration->setProxyDir(sys_get_temp_dir());
            $this->configuration->setProxyNamespace('__TMP__\\ProxyNamespace');

            $this->documentManager = DocumentManager::create($this->client->reveal(), $this->configuration);

            (function () use ($schemaManager): void {
                $this->schemaManager = $schemaManager->reveal();
            })->call($this->documentManager);

            (function () use ($metadataFactory): void {
                $this->metadataFactory = $metadataFactory;
            })->call($this->documentManager);
        }

        return $this->documentManager;
    }
}
