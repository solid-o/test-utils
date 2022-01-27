<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Solido\Common\Exception\UnsupportedResponseObjectException;

use function array_is_list;
use function count;
use function json_decode;
use function mb_strlen;
use function Safe\sprintf;

use const JSON_THROW_ON_ERROR;

final class ResponseLength extends ResponseConstraint
{
    use ResponseJsonContentTrait;

    private int $length;

    public function __construct(int $length)
    {
        $this->length = $length;
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

        if (! $this->isJson($adapter)) {
            return mb_strlen($adapter->getContent()) === $this->length;
        }

        $other = json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($other)) {
            return false;
        }

        return array_is_list($other) && count($other) === $this->length;
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

        $otherContent = $this->isJson($adapter)
            ? json_decode($adapter->getContent(), true, 512, JSON_THROW_ON_ERROR)
            : $adapter->getContent();

        return sprintf(
            '%s has length %u. Actual response content is: %s',
            $this->exporter()->shortenedExport($other),
            $this->length,
            $this->exporter()->export($otherContent),
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
