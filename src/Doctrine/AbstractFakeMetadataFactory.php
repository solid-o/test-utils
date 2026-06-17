<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

use function array_values;
use function class_exists;

/** @implements ClassMetadataFactory<ClassMetadata<object>> */
class AbstractFakeMetadataFactory implements ClassMetadataFactory
{
    /** @var array<class-string, ClassMetadata<object>> */
    private array $metadata;
    protected RuntimeReflectionService $reflectionService;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->metadata = [];
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function setConfiguration(): void
    {
    }

    public function setCacheDriver(): void
    {
    }

    /** @return list<ClassMetadata<object>> */
    public function getAllMetadata(): array
    {
        return array_values($this->metadata);
    }

    /**
     * @param class-string $className
     *
     * @return ClassMetadata<object>
     */
    public function getMetadataFor(string $className): ClassMetadata
    {
        if (class_exists(ClassUtils::class)) {
            $className = ClassUtils::getRealClass($className);
        }

        if (! isset($this->metadata[$className])) {
            throw new MappingException('Cannot find metadata for "' . $className . '"');
        }

        return $this->metadata[$className];
    }

    /**
     * {@inheritDoc}
     *
     * @param class-string $className
     */
    public function hasMetadataFor($className): bool
    {
        if (class_exists(ClassUtils::class)) {
            $className = ClassUtils::getRealClass($className);
        }

        return isset($this->metadata[$className]);
    }

    /**
     * @param class-string $className
     * @param ClassMetadata<object> $class
     */
    public function setMetadataFor(string $className, ClassMetadata $class): void
    {
        if (class_exists(ClassUtils::class)) {
            $className = ClassUtils::getRealClass($className);
        }

        $this->metadata[$className] = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient($className): bool
    {
        return false;
    }
}
