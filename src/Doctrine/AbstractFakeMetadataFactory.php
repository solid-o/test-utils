<?php

declare(strict_types=1);

namespace Solido\TestUtils\Doctrine;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

use function array_values;

class AbstractFakeMetadataFactory implements ClassMetadataFactory
{
    /** @var array<string, ClassMetadata> */
    private array $metadata;
    protected RuntimeReflectionService $reflectionService;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getAllMetadata(): array
    {
        return array_values($this->metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($className): ClassMetadata
    {
        if (! isset($this->metadata[$className])) {
            throw new MappingException('Cannot find metadata for "' . $className . '"');
        }

        return $this->metadata[$className];
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($className): bool
    {
        return isset($this->metadata[$className]);
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataFor($className, $class): void
    {
        $this->metadata[$className] = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className): bool
    {
        return false;
    }
}
