<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use ArrayObject;
use Solido\Common\Exception\UnsupportedResponseObjectException;
use Traversable;

use function is_array;
use function is_string;
use function iterator_to_array;
use function json_decode;
use function Safe\array_replace_recursive;
use function sprintf;
use function str_contains;

use const JSON_THROW_ON_ERROR;

final class ResponseSubset extends ResponseConstraint
{
    use ResponseJsonContentTrait;

    /** @param string|array<string|int, mixed>|object $subset */
    public function __construct(private string|array|object $subset)
    {
    }

    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        if (! $this->isJson($adapter)) {
            return is_string($this->subset) && str_contains($adapter->getContent(), $this->subset);
        }

        $other = json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($other)) {
            return false;
        }

        $this->subset = $this->toArray($this->subset);
        $patched = array_replace_recursive($other, $this->subset);

        return $other === $patched;
    }

    protected function failureDescription(mixed $other): string
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        $otherContent = $this->isJson($adapter)
            ? json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR)
            : $adapter->getContent();

        return sprintf(
            '%s contains subset %s. Actual response content is: %s',
            $this->exporter()->shortenedExport($other),
            $this->exporter()->export($this->subset),
            $this->exporter()->export($otherContent),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'contains subset %s',
            $this->exporter()->export($this->subset),
        );
    }

    /** @return array<string|int, mixed> */
    private function toArray(mixed $other): array
    {
        if (is_array($other)) {
            return $other;
        }

        if ($other instanceof ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof Traversable) {
            return iterator_to_array($other);
        }

        return (array) $other;
    }
}
