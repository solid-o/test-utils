<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Solido\TestUtils\Doctrine\AbstractFakeMetadataFactory;

use function assert;

class FakeMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(EntityManagerInterface $em): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setMetadataFor($className, $class): void
    {
        assert($class instanceof ORMClassMetadata);

        parent::setMetadataFor($className, $class);

        $class->initializeReflection($this->getReflectionService());
        $class->wakeupReflection($this->getReflectionService());
    }
}
