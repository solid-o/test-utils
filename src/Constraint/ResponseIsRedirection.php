<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\Exception\UnsupportedResponseObjectException;

use function Safe\sprintf;

final class ResponseIsRedirection extends ResponseConstraint
{
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

        return $adapter->getStatusCode() >= 300 && $adapter->getStatusCode() < 400;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        try {
            self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        return sprintf('%s %s', $this->exporter()->shortenedExport($other), $this->toString());
    }

    public function toString(): string
    {
        return 'is redirection';
    }
}
