<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use SebastianBergmann\Exporter\Exporter;
use Solido\Common\Exception\UnsupportedResponseObjectException;

use function sprintf;

final class ResponseIsRedirection extends ResponseConstraint
{
    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        return $adapter->getStatusCode() >= 300 && $adapter->getStatusCode() < 400;
    }

    protected function failureDescription(mixed $other): string
    {
        $exporter = new Exporter();

        try {
            self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $exporter->shortenedExport($other));
        }

        return sprintf('%s %s', $exporter->shortenedExport($other), $this->toString());
    }

    public function toString(): string
    {
        return 'is redirection';
    }
}
