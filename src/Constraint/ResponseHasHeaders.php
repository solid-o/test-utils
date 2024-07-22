<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use SebastianBergmann\Exporter\Exporter;
use Solido\Common\Exception\UnsupportedResponseObjectException;

use function array_map;
use function count;
use function implode;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ResponseHasHeaders extends ResponseConstraint
{
    /** @var string[] */
    private array $missing;

    /** @param string[] $headers */
    public function __construct(private readonly array $headers)
    {
        $this->missing = [];
    }

    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        $this->missing = [];
        foreach ($this->headers as $header) {
            if (! empty($adapter->getHeader($header))) {
                continue;
            }

            $this->missing[] = $header;
        }

        return count($this->missing) === 0;
    }

    protected function failureDescription(mixed $other): string
    {
        $exporter = new Exporter();

        try {
            self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $exporter->shortenedExport($other));
        }

        return sprintf(
            '%s has %s header%s',
            $exporter->shortenedExport($other),
            count($this->missing) === 1 ?
                json_encode($this->missing[0], JSON_THROW_ON_ERROR) :
                implode(', ', array_map('json_encode', $this->headers)),
            count($this->missing) === 1 ? '' : 's',
        );
    }

    public function toString(): string
    {
        return sprintf(
            'has %s header%s',
            count($this->headers) === 1 ?
                json_encode($this->headers[0], JSON_THROW_ON_ERROR) :
                implode(', ', array_map('json_encode', $this->headers)),
            count($this->headers) === 1 ? '' : 's',
        );
    }
}
