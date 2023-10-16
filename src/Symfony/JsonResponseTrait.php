<?php

declare(strict_types=1);

namespace Solido\TestUtils\Symfony;

use PHPUnit\Framework\Constraint\LogicalNot;
use Solido\TestUtils\Constraint\JsonResponse;
use Solido\TestUtils\Constraint\JsonResponsePropertiesExist;
use Solido\TestUtils\Constraint\JsonResponsePropertyContains;
use Solido\TestUtils\Constraint\JsonResponsePropertyContainsString;
use Solido\TestUtils\Constraint\JsonResponsePropertyCount;
use Solido\TestUtils\Constraint\JsonResponsePropertyEquals;
use Solido\TestUtils\Constraint\JsonResponsePropertyIsType;
use Throwable;

trait JsonResponseTrait
{
    /**
     * Asserts $response is a JSON response.
     */
    public static function assertJsonResponse(string $message = ''): void
    {
        self::assertThat(static::getResponse(), new JsonResponse(), $message);
    }

    /**
     * Asserts the array of property names are in JSON response.
     *
     * @param string[] $expected
     */
    public static function assertJsonResponsePropertiesExist(array $expected, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new JsonResponsePropertiesExist($expected), $message);
    }

    /**
     * Asserts the specific propertyPath is in the JSON response.
     */
    public static function assertJsonResponsePropertyExists(string $propertyPath, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new JsonResponsePropertiesExist([$propertyPath]), $message);
    }

    /**
     * Asserts the given property path does *not* exist.
     */
    public static function assertJsonResponsePropertyDoesNotExist(string $propertyPath, string $message = ''): void
    {
        self::assertThat(static::getResponse(), new LogicalNot(new JsonResponsePropertiesExist([$propertyPath])), $message);
    }

    /**
     * Asserts the response JSON property equals the given value.
     */
    public static function assertJsonResponsePropertyEquals(mixed $expected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new JsonResponsePropertyEquals($propertyPath, $expected), $message);
    }

    /**
     * Asserts the response JSON property not equals the given value.
     */
    public static function assertJsonResponsePropertyNotEquals(mixed $unexpected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new LogicalNot(new JsonResponsePropertyEquals($propertyPath, $unexpected)), $message);
    }

    /**
     * Asserts the response property is an array.
     *
     * @throws Throwable
     */
    public function assertJsonResponsePropertyIsArray(string $propertyPath, string $message = ''): void
    {
        self::assertJsonResponsePropertyIsType('array', $propertyPath, $message);
    }

    /**
     * Checks it the internal value of a JSON property is of the given type. It uses standard PhpUnit\Assert type-checking.
     *
     * Available values are:
     * 'array'    => true,
     * 'boolean'  => true,
     * 'bool'     => true,
     * 'double'   => true,
     * 'float'    => true,
     * 'integer'  => true,
     * 'int'      => true,
     * 'null'     => true,
     * 'numeric'  => true,
     * 'object'   => true,
     * 'real'     => true,
     * 'resource' => true,
     * 'string'   => true,
     * 'scalar'   => true,
     * 'callable' => true
     */
    public static function assertJsonResponsePropertyIsType(string $expected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new JsonResponsePropertyIsType($propertyPath, $expected), $message);
    }

    /**
     * Asserts the given response property (probably an array) has the expected "count".
     */
    public static function assertJsonResponsePropertyCount(int $expected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new JsonResponsePropertyCount($propertyPath, $expected), $message);
    }

    /**
     * Asserts the specific response property contains the given value.
     *
     * ex:
     *  - ["Hello", "world", "!"] contains "world"
     *  - [{one: "Hello"}] contains (object)['one' => 'Hello']
     */
    public static function assertJsonResponsePropertyContains(mixed $expected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new JsonResponsePropertyContains($propertyPath, $expected), $message);
    }

    /**
     * Asserts the specific response property contains the given value.
     *
     * e.g. "Hello world!" contains "world"
     */
    public static function assertJsonResponsePropertyContainsString(string $expected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new JsonResponsePropertyContainsString($propertyPath, $expected), $message);
    }

    /**
     * Asserts the specific response property does not contain the given value.
     *
     * ex:
     *  - ["Hello", "world", "!"] contains "folks"
     *  - [{one: "Hello"}] contains (object)['two' => 'Hello']
     */
    public static function assertJsonResponsePropertyNotContains(mixed $unexpected, string $propertyPath, string $message = ''): void
    {
        self::assertThat(self::getResponse(), new LogicalNot(new JsonResponsePropertyContains($propertyPath, $unexpected)), $message);
    }
}
