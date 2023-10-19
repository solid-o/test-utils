<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Solido\TestUtils\Doctrine\AbstractFakeMetadataFactory;

use function assert;

class FakeMetadataFactory extends AbstractFakeMetadataFactory
{
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setMetadataFor($className, $class): void
    {
        assert($class instanceof ORMClassMetadata);

        parent::setMetadataFor($className, $class);

        $class->initializeReflection($this->reflectionService);
        $class->wakeupReflection($this->reflectionService);
    }
}
