<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use JsonException;
use PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider;
use Solido\Common\Exception\UnsupportedResponseObjectException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function json_decode;
use function Safe\preg_match;
use function Safe\sprintf;

use const JSON_THROW_ON_ERROR;

abstract class AbstractJsonResponseContent extends ResponseConstraint
{
    use JsonResponseTrait;

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
        } catch (JsonException $e) {
            return false;
        }

        return $this->doMatch($data, $accessor);
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

        $contentType = $adapter->getContentType();
        if (! preg_match('/application\/json/', $contentType)) {
            return sprintf('%s has json content type', $this->exporter()->shortenedExport($other));
        }

        $accessor = self::getPropertyAccessor();
        $content = $adapter->getContent();
        if ($content !== '') {
            /** @phpstan-ignore-next-line */
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
            $error
        );
    }

    /**
     * Whether the constraint matches against data.
     *
     * @param mixed $data
     */
    abstract protected function doMatch($data, PropertyAccessorInterface $accessor): bool;

    /**
     * The failure description
     *
     * @param mixed $other
     */
    abstract protected function getFailureDescription($other, PropertyAccessorInterface $accessor): string;
}
