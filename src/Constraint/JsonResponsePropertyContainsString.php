<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Exporter\Exporter;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use TypeError;

use function get_debug_type;
use function is_object;
use function is_string;
use function method_exists;
use function sprintf;

class JsonResponsePropertyContainsString extends AbstractJsonResponseContent
{
    private string $expected;

    public function __construct(private readonly string $propertyPath, mixed $expected)
    {
        if (is_object($expected) && method_exists($expected, '__toString')) {
            $expected = (string) $expected;
        }

        if (! is_string($expected)) {
            throw new TypeError(sprintf('Expected a string or a stringable object, %s passed', get_debug_type($expected)));
        }

        $this->expected = $expected;
    }

    protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool
    {
        $other = self::readProperty($accessor, $data, $this->propertyPath);
        if (! is_string($other)) {
            throw new ExpectationFailedException(sprintf('Property "%s" is not a string (%s)', $this->propertyPath, get_debug_type($other)));
        }

        return (new StringContains($this->expected))->matches($other);
    }

    protected function getFailureDescription(mixed $other, PropertyAccessorInterface $accessor): string
    {
        $other = self::readProperty($accessor, $other, $this->propertyPath);
        $exporter = new Exporter();

        return sprintf(
            'property "%s" (%s) contains %s',
            $this->propertyPath,
            $exporter->export($other),
            $exporter->export($this->expected),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'property "%s" contains %s',
            $this->propertyPath,
            (new Exporter())->export($this->expected),
        );
    }
}
