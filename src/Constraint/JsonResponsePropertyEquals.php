<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function Safe\sprintf;

class JsonResponsePropertyEquals extends AbstractJsonResponseContent
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

        try {
            (new IsEqual($this->expected))->evaluate($other);
        } catch (ExpectationFailedException $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getFailureDescription($other, PropertyAccessorInterface $accessor): string
    {
        $other = self::readProperty($accessor, $other, $this->propertyPath);

        return sprintf(
            'property "%s" (%s) is equal to %s',
            $this->propertyPath,
            $this->exporter()->shortenedExport($other),
            $this->exporter()->shortenedExport($this->expected),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'property "%s" is equal to %s',
            $this->propertyPath,
            $this->exporter()->shortenedExport($this->expected),
        );
    }
}
