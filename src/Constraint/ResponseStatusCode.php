<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\Exception\UnsupportedResponseObjectException;
use Symfony\Component\HttpFoundation\Response;

use function class_exists;
use function count;
use function implode;
use function in_array;
use function Safe\sprintf;

final class ResponseStatusCode extends ResponseConstraint
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
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
            return false;
        }

        return in_array($adapter->getStatusCode(), $this->validCodes, true);
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

        $statusCode = $adapter->getStatusCode();

        return sprintf(
            '%s status code (%u%s) is %s',
            $this->exporter()->shortenedExport($other),
            $statusCode,
            class_exists(Response::class) && isset(Response::$statusTexts[$statusCode])
                ? ' ' . Response::$statusTexts[$statusCode]
                : '',
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
