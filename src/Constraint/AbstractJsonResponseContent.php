<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\Constraint\JsonMatchesErrorMessageProvider;
use PHPUnit\Framework\ExpectationFailedException;
use Solido\Common\Exception\UnsupportedResponseObjectException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathIterator;

use function array_keys;
use function array_map;
use function get_object_vars;
use function implode;
use function is_array;
use function json_decode;
use function json_last_error;
use function Safe\preg_match;
use function Safe\sprintf;

use const JSON_ERROR_NONE;

abstract class AbstractJsonResponseContent extends ResponseConstraint
{
    /**
     * Returns a valid property accessor.
     */
    private static function getPropertyAccessor(): PropertyAccessorInterface
    {
        static $accessor = null;
        if ($accessor === null) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        return $accessor;
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

        $contentType = $adapter->getContentType();
        if (! preg_match('/application\/json/', $contentType)) {
            return false;
        }

        $accessor = self::getPropertyAccessor();
        $content = $adapter->getContent();
        if ($content === '') {
            return false;
        }

        /** @phpstan-ignore-next-line */
        $data = json_decode($content);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
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
            $value = json_decode($content);
            $error = json_last_error();
            if ($error === JSON_ERROR_NONE) {
                return $this->getFailureDescription($value, $accessor);
            }

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

    /**
     * Decodes a Response content into a JSON and reads its properties, given a propertyPath.
     * It uses a PropertyAccessor to access the fields, so it accepts propertyPath values formatted as.
     *
     * 'children[0].firstName'
     * 'children.son.nephew.fieldName'
     *
     * @see http://symfony.com/doc/current/components/property_access.html
     *
     * This will throw Exception if the value does not exist
     *
     * @param mixed $data
     * @param string $propertyPath e.g. firstName, battles[0].programmer.username
     *
     * @return mixed
     */
    protected static function readProperty(PropertyAccessorInterface $accessor, $data, string $propertyPath)
    {
        if ($propertyPath === '.') {
            return $data;
        }

        try {
            return $accessor->getValue($data, $propertyPath);
        } catch (AccessException | UnexpectedTypeException $e) {
            $values = is_array($data) ? $data : get_object_vars($data);
            if (! $values) {
                $error = is_array($data) ? 'from empty array' : 'from empty object';
            } else {
                $pathIterator = new PropertyPathIterator(new PropertyPath($propertyPath));
                $path = [];
                $zval = $data;

                foreach ($pathIterator as $segment) {
                    $segment = $pathIterator->isIndex() ? '[' . $segment . ']' : $segment;
                    if (! $accessor->isReadable($zval, $segment)) {
                        $path[] = $segment . ' [ERROR. Available keys: ' . implode(
                            ', ',
                            array_map('json_encode', array_keys(is_array($zval) ? $zval : get_object_vars($zval)))
                        ) . ']';
                        break;
                    }

                    $zval = $accessor->getValue($zval, $segment);
                    $path[] = $segment;
                }

                $error = 'at path ' . implode(' -> ', $path);
            }

            throw new ExpectationFailedException(sprintf('Error reading property "%s" %s', $propertyPath, $error), null, $e);
        }
    }
}
