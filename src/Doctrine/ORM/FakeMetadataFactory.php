<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;

class FakeMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(EntityManagerInterface $em): void
    {
    }

    /**
     * @param class-string $className
     * @param ORMClassMetadata<object> $class
     */
    public function setMetadataFor(string $className, ClassMetadata $class): void
    {
        parent::setMetadataFor($className, $class);

        $class->initializeReflection($this->getReflectionService());
        $class->wakeupReflection($this->getReflectionService());
    }
}
