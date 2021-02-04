<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

use function Safe\sprintf;

final class ResponseIsSuccessful extends Constraint
{
    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        if (! $other instanceof Response) {
            return false;
        }

        return $other->isSuccessful();
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
        return 'is successful';
    }
}
