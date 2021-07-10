<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider;
use Solido\Common\Exception\UnsupportedResponseObjectException;

use function json_decode;
use function json_last_error;
use function Safe\preg_match;
use function Safe\sprintf;

use const JSON_ERROR_NONE;

final class JsonResponse extends ResponseConstraint
{
    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
            if (! preg_match('/application\/json/', $adapter->getContentType())) {
                return false;
            }
        } catch (UnsupportedResponseObjectException $e) {
            return false;
        }

        $content = $adapter->getContent();
        if ($content === '') {
            return false;
        }

        /** @phpstan-ignore-next-line */
        @json_decode($content);

        return json_last_error() === JSON_ERROR_NONE;
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

        if (! preg_match('/application\/json/', $adapter->getContentType())) {
            return sprintf('%s has json content type', $this->exporter()->shortenedExport($other));
        }

        $content = $adapter->getContent();
        if ($content !== '') {
            /** @phpstan-ignore-next-line */
            json_decode($content);
            $error = JsonMatchesErrorMessageProvider::determineJsonError(
                (string) json_last_error()
            );
        } else {
            $error = 'Empty response';
        }

        return sprintf(
            '%s is valid JSON response (%s)',
            $this->exporter()->shortenedExport($other),
            $error
        );
    }

    public function toString(): string
    {
        return 'is valid JSON response';
    }
}
