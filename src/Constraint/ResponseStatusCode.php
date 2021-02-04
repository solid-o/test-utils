<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function implode;
use function in_array;
use function Safe\sprintf;

final class ResponseStatusCode extends Constraint
{
    /** @var int[] */
    private array $validCodes;

    public function __construct(int ...$validCodes)
    {
        $this->validCodes = $validCodes;
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        if (! $other instanceof Response) {
            return false;
        }

        return in_array($other->getStatusCode(), $this->validCodes, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        if (! $other instanceof Response) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        $statusCode = $other->getStatusCode();

        return sprintf(
            '%s status code (%u%s) is %s',
            $this->exporter()->shortenedExport($other),
            $statusCode,
            isset(Response::$statusTexts[$statusCode]) ? ' ' . Response::$statusTexts[$statusCode] : '',
            count($this->validCodes) === 1 ?
                'equal to ' . $this->validCodes[0] :
                ('in (' . implode(', ', $this->validCodes) . ')'),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'status code is %s',
            count($this->validCodes) === 1 ?
                'equal to ' . $this->validCodes[0] :
                ('in (' . implode(', ', $this->validCodes) . ')'),
        );
    }
}
