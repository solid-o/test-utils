<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use JsonException;
use PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider;
use Solido\Common\Exception\UnsupportedResponseObjectException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function json_decode;
use function Safe\preg_match;
use function sprintf;

use const JSON_THROW_ON_ERROR;

abstract class AbstractJsonResponseContent extends ResponseConstraint
{
    use JsonResponseTrait;

    protected function matches(mixed $other): bool
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException) {
            return false;
        }

        $contentType = $adapter->getContentType();
        if (! preg_match('/application\/json/', $contentType)) {
            return false;
        }

        $accessor = self::getPropertyAccessor();
        $content = $adapter->getContent();
        if ($content === '') {
            return false;
        }

        try {
            $data = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        return $this->doMatch($data, $accessor);
    }

    protected function failureDescription(mixed $other): string
    {
        try {
            $adapter = self::getResponseAdapter($other);
        } catch (UnsupportedResponseObjectException $e) {
            return sprintf('%s is a response object', $this->exporter()->shortenedExport($other));
        }

        $contentType = $adapter->getContentType();
        if (! preg_match('/application\/json/', $contentType)) {
            return sprintf('%s has json content type', $this->exporter()->shortenedExport($other));
        }

        $accessor = self::getPropertyAccessor();
        $content = $adapter->getContent();
        if ($content !== '') {
            try {
                $value = json_decode($content, false, 512, JSON_THROW_ON_ERROR);

                return $this->getFailureDescription($value, $accessor);
            } catch (JsonException $e) {
                $error = JsonMatchesErrorMessageProvider::determineJsonError((string) $e->getCode());
            }
        } else {
            $error = 'Empty response';
        }

        return sprintf(
            '%s is valid JSON response (%s)',
            $this->exporter()->shortenedExport($other),
            $error,
        );
    }

    /**
     * Whether the constraint matches against data.
     */
    abstract protected function doMatch(mixed $data, PropertyAccessorInterface $accessor): bool;

    /**
     * The failure description
     */
    abstract protected function getFailureDescription(mixed $other, PropertyAccessorInterface $accessor): string;
}
