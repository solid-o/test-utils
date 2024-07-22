<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\IsType;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_debug_type;
use function sprintf;

class JsonResponsePropertyIsType extends AbstractJsonResponseContent
{
    public function __construct(private readonly string $propertyPath, private string $expected)
    {
    }

    protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool
    {
        $other = self::readProperty($accessor, $data, $this->propertyPath);

        return (new IsType($this->expected))->matches($other); /* @phpstan-ignore-line */
    }

    protected function getFailureDescription(mixed $other, PropertyAccessorInterface $accessor): string
    {
        $other = self::readProperty($accessor, $other, $this->propertyPath);

        return sprintf(
            'property "%s" (%s) is of type %s',
            $this->propertyPath,
            get_debug_type($other),
            $this->expected,
        );
    }

    public function toString(): string
    {
        return sprintf(
            'property "%s" is of type %s',
            $this->propertyPath,
            $this->expected,
        );
    }
}
