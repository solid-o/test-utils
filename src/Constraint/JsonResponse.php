<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use JsonException;
use SebastianBergmann\Exporter\Exporter;
use Solido\Common\Exception\UnsupportedResponseObjectException;

use function json_decode;
use function Safe\preg_match;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class JsonResponse extends ResponseConstraint
{
    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
            if (! preg_match('/application\/json/', $adapter->getContentType())) {
                return false;
            }
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        $content = $adapter->getContent();
        try {
            json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        return true;
    }

    protected function failureDescription(mixed $other): string
    {
        $exporter = new Exporter();

        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return sprintf('%s is a response object', $exporter->shortenedExport($other));
        }

        if (! preg_match('/application\/json/', $adapter->getContentType())) {
            return sprintf('%s has json content type', $exporter->shortenedExport($other));
        }

        $content = $adapter->getContent();
        $error = 'Empty response';

        if ($content !== '') {
            try {
                json_decode($content, false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $error = JsonMatchesErrorMessageProvider::determineJsonError($e->getCode());
            }
        }

        return sprintf(
            '%s is valid JSON response (%s)',
            $exporter->shortenedExport($other),
            $error,
        );
    }

    public function toString(): string
    {
        return 'is valid JSON response';
    }
}
