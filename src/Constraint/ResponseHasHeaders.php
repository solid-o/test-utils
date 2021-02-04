<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

use function array_map;
use function count;
use function implode;
use function json_encode;
use function Safe\sprintf;

use const JSON_THROW_ON_ERROR;

final class ResponseHasHeaders extends Constraint
{
    /** @var string[] */
    private array $headers;

    /** @var string[] */
    private array $missing;

    /**
     * @param string[] $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
        $this->missing = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        if (! $other instanceof Response) {
            return false;
        }

        $this->missing = [];
        foreach ($this->headers as $header) {
            if ($other->headers->has($header)) {
                continue;
            }

            $this->missing[] = $header;
        }

        return count($this->missing) === 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        if (! $other instanceof Response) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        return sprintf(
            '%s has %s header%s',
            $this->exporter()->shortenedExport($other),
            count($this->missing) === 1 ?
                json_encode((string) $this->missing[0], JSON_THROW_ON_ERROR) :
                implode(', ', array_map('json_encode', $this->headers)),
            count($this->missing) === 1 ? '' : 's'
        );
    }

    public function toString(): string
    {
        return sprintf(
            'has %s header%s',
            count($this->headers) === 1 ?
                json_encode((string) $this->headers[0], JSON_THROW_ON_ERROR) :
                implode(', ', array_map('json_encode', $this->headers)),
            count($this->headers) === 1 ? '' : 's'
        );
    }
}
