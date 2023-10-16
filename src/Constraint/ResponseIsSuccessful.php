<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\Exception\UnsupportedResponseObjectException;

use function sprintf;

final class ResponseIsSuccessful extends ResponseConstraint
{
    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        return $adapter->getStatusCode() >= 200 && $adapter->getStatusCode() < 300;
    }

    protected function failureDescription(mixed $other): string
    {
        try {
            self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        return sprintf('%s %s', $this->exporter()->shortenedExport($other), $this->toString());
    }

    public function toString(): string
    {
        return 'is successful';
    }
}
