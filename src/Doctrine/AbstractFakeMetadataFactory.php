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

class AbstractFakeMetadataFactory implements ClassMetadataFactory
{
    /** @var array<string, ClassMetadata> */
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

    /**
     * {@inheritDoc}
     */
    public function getAllMetadata(): array
    {
        return array_values($this->metadata);
    }

    /**
     * {@inheritDoc}
     *
     * @param class-string $className
     */
    public function getMetadataFor($className): ClassMetadata
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
     * {@inheritDoc}
     *
     * @param class-string $className
     */
    public function setMetadataFor($className, $class): void
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
