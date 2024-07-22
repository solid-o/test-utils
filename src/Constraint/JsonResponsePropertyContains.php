<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\TraversableContainsEqual;
use PHPUnit\Framework\Constraint\TraversableContainsIdentical;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Exporter\Exporter;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_debug_type;
use function is_array;
use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class JsonResponsePropertyContains extends AbstractJsonResponseContent
{
    private mixed $expected;

    public function __construct(private readonly string $propertyPath, mixed $expected)
    {
        $this->expected = json_decode(json_encode($expected, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
    }

    protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool
    {
        if (! is_array($data) && ! $data instanceof stdClass) {
            throw new ExpectationFailedException(sprintf('Property "%s" is not an array (%s)', $this->propertyPath, get_debug_type($data)));
        }

        $other = self::readProperty($accessor, $data, $this->propertyPath);
        $constraint = $this->expected instanceof stdClass ?
            new TraversableContainsEqual($this->expected) :
            new TraversableContainsIdentical($this->expected);

        /** @phpstan-ignore-next-line */
        return $constraint->matches($other);
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
            (new Exporter())->shortenedExport($this->expected),
        );
    }
}
