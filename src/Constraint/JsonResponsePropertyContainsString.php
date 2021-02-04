<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_debug_type;
use function is_string;
use function Safe\sprintf;

class JsonResponsePropertyContainsString extends AbstractJsonResponseContent
{
    private string $propertyPath;

    /** @var mixed */
    private $expected;

    /**
     * @param mixed $expected
     */
    public function __construct(string $propertyPath, $expected)
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
        if (! is_string($other)) {
            throw new ExpectationFailedException(sprintf('Property "%s" is not a string (%s)', $this->propertyPath, get_debug_type($other)));
        }

        return (new StringContains($this->expected))->matches($other);
    }

    /**
     * @inheritDoc
     */
    protected function getFailureDescription($other, PropertyAccessorInterface $accessor): string
    {
        $other = self::readProperty($accessor, $other, $this->propertyPath);

        return sprintf(
            'property "%s" (%s) contains %s',
            $this->propertyPath,
            $this->exporter()->export($other),
            $this->exporter()->export($this->expected),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'property "%s" contains %s',
            $this->propertyPath,
            $this->exporter()->export($this->expected),
        );
    }
}
