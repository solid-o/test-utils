<?php

declare(strict_types=1);

namespace Solido\TestUtils\Elastica;

use Doctrine\Persistence\Mapping\MappingException;
use Kcs\Metadata\ClassMetadataInterface;
use Refugis\ODM\Elastica\Metadata\DocumentMetadata;
use Refugis\ODM\Elastica\Metadata\MetadataFactory;

use function array_values;
use function is_object;

class FakeMetadataFactory extends MetadataFactory
{
    /** @var array<class-string, DocumentMetadata<object>> */
    private array $metadata;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
    }

    /** @return list<DocumentMetadata<object>> */
    public function getAllMetadata(): array
    {
        return array_values($this->metadata);
    }

    public function getMetadataFor(mixed $className): ClassMetadataInterface
    {
        if (is_object($className)) {
            $className = $className::class;
        }

        if (! isset($this->metadata[$className])) {
            throw new MappingException('Cannot find metadata for "' . $className . '"');
        }

        return $this->metadata[$className];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetadataFor($className): bool
    {
        if (is_object($className)) {
            $className = $className::class;
        }

        return isset($this->metadata[$className]);
    }

    /**
     * @param class-string $className
     * @param DocumentMetadata<object> $class
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function setMetadataFor($className, $class): void
    {
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
