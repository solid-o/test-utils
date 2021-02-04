<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Count;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function Safe\sprintf;

class JsonResponsePropertyCount extends AbstractJsonResponseContent
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

        return (new Count($this->expected))->matches($other);
    }

    /**
     * @inheritDoc
     */
    protected function getFailureDescription($other, PropertyAccessorInterface $accessor): string
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
