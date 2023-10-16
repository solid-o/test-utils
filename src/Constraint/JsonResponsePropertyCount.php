<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Count;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function sprintf;

class JsonResponsePropertyCount extends AbstractJsonResponseContent
{
    public function __construct(private readonly string $propertyPath, private int $expected)
    {
    }

    protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool
    {
        $other = self::readProperty($accessor, $data, $this->propertyPath);

        return (new Count($this->expected))->matches($other);
    }

    protected function getFailureDescription(mixed $other, PropertyAccessorInterface $accessor): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return sprintf(
            'property "%s" count matches %u',
            $this->propertyPath,
            $this->expected,
        );
    }
}
