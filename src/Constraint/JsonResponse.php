<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;
use function json_last_error;
use function Safe\preg_match;
use function Safe\sprintf;

use const JSON_ERROR_NONE;

final class JsonResponse extends Constraint
{
    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        if (
            ! $other instanceof Response ||
            ! $other->headers->has('Content-Type') ||
            ! preg_match('/application\/json/', (string) $other->headers->get('Content-Type', ''))
        ) {
            return false;
        }

        $content = $other->getContent();
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
        if (! $other instanceof Response) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        if (
            ! $other->headers->has('Content-Type') ||
            ! preg_match('/application\/json/', (string) $other->headers->get('Content-Type', ''))
        ) {
            return sprintf('%s has json content type', $this->exporter()->shortenedExport($other));
        }

        $content = $other->getContent();
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
