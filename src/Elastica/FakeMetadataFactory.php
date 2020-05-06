<?php

declare(strict_types=1);

namespace Solido\TestUtils\Elastica;

use Doctrine\Persistence\Mapping\MappingException;
use Kcs\Metadata\ClassMetadataInterface;
use Refugis\ODM\Elastica\Metadata\DocumentMetadata;
use Refugis\ODM\Elastica\Metadata\MetadataFactory;
use function array_values;
use function assert;
use function get_class;
use function is_object;

class FakeMetadataFactory extends MetadataFactory
{
    /** @var array<string, DocumentMetadata> */
    private array $metadata;

    /**
     * {@inheritdoc}
     */
    public function __construct()
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
    public function getMetadataFor($className): ClassMetadataInterface
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

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
        if (is_object($className)) {
            $className = get_class($className);
        }

        return isset($this->metadata[$className]);
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataFor($className, $class): void
    {
        assert($class instanceof DocumentMetadata);
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
