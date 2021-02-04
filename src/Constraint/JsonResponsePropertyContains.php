<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\TraversableContainsEqual;
use PHPUnit\Framework\Constraint\TraversableContainsIdentical;
use PHPUnit\Framework\ExpectationFailedException;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_debug_type;
use function is_array;
use function json_decode;
use function json_encode;
use function Safe\sprintf;

use const JSON_THROW_ON_ERROR;

class JsonResponsePropertyContains extends AbstractJsonResponseContent
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
        $this->expected = json_decode(json_encode($expected, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     */
    protected function doMatch($data, PropertyAccessorInterface $accessor): bool
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
            $this->exporter()->shortenedExport($this->expected),
        );
    }
}
