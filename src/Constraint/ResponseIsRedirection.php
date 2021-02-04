<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

use function Safe\sprintf;

final class ResponseIsRedirection extends Constraint
{
    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        if (! $other instanceof Response) {
            return false;
        }

        return $other->isRedirection();
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        if (! $other instanceof Response) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        return sprintf('%s %s', $this->exporter()->shortenedExport($other), $this->toString());
    }

    public function toString(): string
    {
        return 'is redirection';
    }
}
