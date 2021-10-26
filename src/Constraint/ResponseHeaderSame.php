<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\Exception\UnsupportedResponseObjectException;

use function json_encode;
use function Safe\sprintf;

use const JSON_THROW_ON_ERROR;

final class ResponseHeaderSame extends ResponseConstraint
{
    private string $header;
    private string $value;

    public function __construct(string $header, string $value)
    {
        $this->header = $header;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
            return false;
        }

        $header = $adapter->getHeader($this->header)[0] ?? null;

        return $header === $this->value;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
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
            json_encode($this->value, JSON_THROW_ON_ERROR)
        );
    }
}
