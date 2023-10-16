<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\Exception\UnsupportedResponseObjectException;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ResponseHeaderSame extends ResponseConstraint
{
    public function __construct(private readonly string $header, private readonly string $value)
    {
    }

    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        $header = $adapter->getHeader($this->header)[0] ?? null;

        return $header === $this->value;
    }

    protected function failureDescription(mixed $other): string
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        $header = $adapter->getHeader($this->header)[0] ?? null;

        return sprintf(
            '%s has header %s with value %s (%s)',
            $this->exporter()->shortenedExport($other),
            json_encode($this->header, JSON_THROW_ON_ERROR),
            json_encode($this->value, JSON_THROW_ON_ERROR),
            $header === null ? 'header missing' : 'actual value: ' . json_encode($header, JSON_THROW_ON_ERROR),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'has header %s with value %s',
            json_encode($this->header, JSON_THROW_ON_ERROR),
            json_encode($this->value, JSON_THROW_ON_ERROR),
        );
    }
}
