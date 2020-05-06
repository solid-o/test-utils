<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\Mongo;

use Doctrine\ODM\MongoDB\DocumentManager;
use Solido\TestUtils\Doctrine\AbstractFakeMetadataFactory;

class FakeMetadataFactory extends AbstractFakeMetadataFactory
{
    public function setDocumentManager(DocumentManager $documentManager): void
    {
    }
}
