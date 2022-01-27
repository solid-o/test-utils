<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\IsType;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_debug_type;
use function Safe\sprintf;

class JsonResponsePropertyIsType extends AbstractJsonResponseContent
{
    private string $propertyPath;
    private string $expected;

    public function __construct(string $propertyPath, string $expected)
    {
        $this->propertyPath = $propertyPath;
        $this->expected = $expected;
    }

    /**
     * @inheritDoc
     */
    protected function doMatch($data, PropertyAccessorInterface $accessor): bool
    {
        $other = self::readProperty($accessor, $data, $this->propertyPath);

        return (new IsType($this->expected))->matches($other);
    }

    /**
     * @inheritDoc
     */
    protected function getFailureDescription($other, PropertyAccessorInterface $accessor): string
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
