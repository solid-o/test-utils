<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathIterator;
use TypeError;

use function array_keys;
use function array_map;
use function get_debug_type;
use function get_object_vars;
use function implode;
use function is_array;
use function is_object;
use function Safe\sprintf;

/**
 * @internal
 */
trait JsonResponseTrait
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

        if (! is_array($data) && ! is_object($data)) {
            throw new TypeError(sprintf('Error reading property: expected an array or an object, %s given', get_debug_type($data)));
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
                    /* @phpstan-ignore-next-line */
                    if (! $accessor->isReadable($zval, $segment)) {
                        if (! is_array($zval) && ! is_object($zval)) {
                            $path[] = $segment . ' [ERROR. Not traversable (' . get_debug_type($zval) . ') value]';
                        } else {
                            $path[] = $segment . ' [ERROR. Available keys: ' . implode(
                                ', ',
                                array_map('json_encode', array_keys(is_array($zval) ? $zval : get_object_vars($zval)))
                            ) . ']';
                        }

                        break;
                    }

                    assert(is_array($zval) || is_object($zval));
                    $zval = $accessor->getValue($zval, $segment);
                    $path[] = $segment;
                }

                $error = 'at path ' . implode(' -> ', $path);
            }

            throw new ExpectationFailedException(sprintf('Error reading property "%s" %s', $propertyPath, $error), null, $e);
        }
    }
}
