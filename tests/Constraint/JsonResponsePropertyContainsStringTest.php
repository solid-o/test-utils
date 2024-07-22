<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponsePropertyContainsString;
use Symfony\Component\HttpFoundation\Response;

class JsonResponsePropertyContainsStringTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches($expected, $response, $path, string $contained, $message = ''): void
    {
        $constraint = new JsonResponsePropertyContainsString($path, $contained);

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
        yield [false, null, '.', 'foo', 'Failed asserting that null is a response object.'];
        yield [false, true, '.', 'foo', 'Failed asserting that true is a response object.'];
        yield [false, '', '.', 'foo', "Failed asserting that '' is a response object."];
        yield [false, new Response(), '.', 'foo', 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Empty response).',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo.foo',
            'foo',
            'Error reading property "foo.foo" from empty object',
        ];

        yield [
            false,
            new Response('["foo"]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo.foo',
            'foo',
            'Error reading property "foo.foo" at path foo [ERROR. Available keys: 0]',
        ];

        yield [
            false,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0].bar',
            'foo',
            'Error reading property "[0].bar" at path [0] -> bar [ERROR. Available keys: "foo"]',
        ];

        yield [
            false,
            new Response('{"foo":{"bar":"foo","baz":true}}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo.foo',
            'foo',
            'Error reading property "foo.foo" at path foo -> foo [ERROR. Available keys: "bar", "baz"]',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            'Property "." is not a string (stdClass)',
        ];

        yield [
            false,
            new Response('[]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            'Property "." is not a string (array)',
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0].foo',
            'bar',
        ];

        yield [
            true,
            new Response('["bar"]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0]',
            'ba',
        ];

        yield [
            false,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            'foo',
            'Failed asserting that property "foo" (\'bar\') contains \'foo\'.',
        ];

        yield [
            true,
            new Response('{"foo":{"bar":"bar"}}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo.bar',
            'bar',
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];
    }

    public function testToString(): void
    {
        $constraint = new JsonResponsePropertyContainsString('foo', 'foo');
        self::assertEquals('property "foo" contains \'foo\'', $constraint->toString());
    }
}
