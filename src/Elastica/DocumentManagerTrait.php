<?php

declare(strict_types=1);

namespace Solido\TestUtils\Elastica;

use Elastica\Client;
use Elastica\Index;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Refugis\ODM\Elastica\Builder;
use Refugis\ODM\Elastica\DocumentManagerInterface;

trait DocumentManagerTrait
{
    use ProphecyTrait;

    private ?DocumentManagerInterface $documentManager;

    /** @var Client|ObjectProphecy */
    private object $client;

    public function getDocumentManager(): DocumentManagerInterface
    {
        if ($this->documentManager === null) {
            $this->client = $this->prophesize(Client::class);
            $builder = new Builder();

            $builder
                ->setClient($this->client->reveal())
                ->setMetadataFactory(new FakeMetadataFactory());

            $this->client->getIndex(Argument::type('string'))->will(function ($args) {
                return new Index($this->reveal(), $args[0]);
            });

            $this->documentManager = $builder->build();
        }

        return $this->documentManager;
    }
}
