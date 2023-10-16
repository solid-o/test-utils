<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function array_map;
use function count;
use function implode;
use function is_array;
use function is_object;
use function sprintf;

class JsonResponsePropertiesExist extends AbstractJsonResponseContent
{
    /** @var string[] */
    private array $missing;

    /** @param string[] $propertyPaths */
    public function __construct(private readonly array $propertyPaths)
    {
        $this->missing = [];
    }

    protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool
    {
        if (! is_array($data) && ! is_object($data)) {
            $this->missing = $this->propertyPaths;

            return false;
        }

        $this->missing = [];
        foreach ($this->propertyPaths as $propertyPath) {
            if ($accessor->isReadable($data, $propertyPath)) {
                continue;
            }

            $this->missing[] = $propertyPath;
        }

        return count($this->missing) === 0;
    }

    protected function getFailureDescription(mixed $other, PropertyAccessorInterface $accessor): string
    {
        return sprintf(
            'propert%s %s exist%s',
            count($this->missing) === 1 ? 'y' : 'ies',
            implode(', ', array_map('json_encode', $this->missing)),
            count($this->missing) === 1 ? 's' : '',
        );
    }

    public function toString(): string
    {
        return sprintf(
            'propert%s %s exist%s',
            count($this->propertyPaths) === 1 ? 'y' : 'ies',
            implode(', ', array_map('json_encode', $this->propertyPaths)),
            count($this->propertyPaths) === 1 ? 's' : '',
        );
    }
}
