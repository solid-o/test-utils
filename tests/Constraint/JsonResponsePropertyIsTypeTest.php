<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponsePropertyIsType;
use Symfony\Component\HttpFoundation\Response;

class JsonResponsePropertyIsTypeTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches($expected, $response, $path, string $type, $message = ''): void
    {
        $constraint = new JsonResponsePropertyIsType($path, $type);

        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($message);
        } else {
            $this->addToAssertionCount(1);
        }

        $constraint->evaluate($response);
    }

    public static function matchesProvider(): iterable
    {
        yield [false, null, '.', 'string', 'Failed asserting that null is a response object.'];
        yield [false, true, '.', 'string', 'Failed asserting that true is a response object.'];
        yield [false, '', '.', 'string', "Failed asserting that '' is a response object."];
        yield [false, new Response(), '.', 'string', 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'string',
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Empty response).',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'array',
            'Failed asserting that property "." (stdClass) is of type array.',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'int',
            'Failed asserting that property "." (stdClass) is of type int.',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'string',
            'Failed asserting that property "." (stdClass) is of type string.',
        ];

        yield [
            false,
            new Response('null', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'object',
            'Failed asserting that property "." (null) is of type object.',
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'array',
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0]',
            'object',
        ];

        yield [
            true,
            new Response('{"foo":["bar","bar"]}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            'array',
        ];

        yield [
            true,
            new Response('{"foo":{"bar":"bar"}}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            'object',
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            '',
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];
    }

    public function testToString(): void
    {
        $constraint = new JsonResponsePropertyIsType('foo', 'stdClass');
        self::assertEquals('property "foo" is of type stdClass', $constraint->toString());
    }
}
