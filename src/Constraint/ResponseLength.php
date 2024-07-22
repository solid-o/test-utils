<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use SebastianBergmann\Exporter\Exporter;
use Solido\Common\Exception\UnsupportedResponseObjectException;

use function array_is_list;
use function count;
use function is_array;
use function json_decode;
use function mb_strlen;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ResponseLength extends ResponseConstraint
{
    use ResponseJsonContentTrait;

    public function __construct(private readonly int $length)
    {
    }

    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        if (! $this->isJson($adapter)) {
            return mb_strlen($adapter->getContent()) === $this->length;
        }

        $other = json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($other)) {
            return false;
        }

        return array_is_list($other) && count($other) === $this->length;
    }

    protected function failureDescription(mixed $other): string
    {
        $exporter = new Exporter();

        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $exporter->shortenedExport($other));
        }

        $otherContent = $this->isJson($adapter)
            ? json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR)
            : $adapter->getContent();

        return sprintf(
            '%s has length %u. Actual response content is: %s',
            $exporter->shortenedExport($other),
            $this->length,
            $exporter->export($otherContent),
        );
    }

    public function toString(): string
    {
        return sprintf(
            'has length %u',
            $this->length,
        );
    }
}
