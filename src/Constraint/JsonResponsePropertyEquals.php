<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Exporter\Exporter;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function sprintf;

class JsonResponsePropertyEquals extends AbstractJsonResponseContent
{
    public function __construct(private readonly string $propertyPath, private mixed $expected)
    {
    }

    protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool
    {
        $other = self::readProperty($accessor, $data, $this->propertyPath);

        try {
            (new IsEqual($this->expected))->evaluate($other);
        } catch (ExpectationFailedException) {
            return false;
        }

        return true;
    }

    protected function getFailureDescription(mixed $other, PropertyAccessorInterface $accessor): string
    {
        $exporter = new Exporter();
        $other = self::readProperty($accessor, $other, $this->propertyPath);

        return sprintf(
            'property "%s" (%s) is equal to %s',
            $this->propertyPath,
            $exporter->shortenedExport($other),
            $exporter->shortenedExport($this->expected),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'property "%s" is equal to %s',
            $this->propertyPath,
            (new Exporter())->shortenedExport($this->expected),
        );
    }
}
