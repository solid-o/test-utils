<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponsePropertyEquals;
use Symfony\Component\HttpFoundation\Response;

class JsonResponsePropertyEqualsTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($expected, $response, $path, $comperand, $message = ''): void
    {
        $constraint = new JsonResponsePropertyEquals($path, $comperand);

        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessageMatches('/^' . preg_quote($message, '/') . '$/');
        } else {
            $this->addToAssertionCount(1);
        }

        $constraint->evaluate($response);
    }

    public function matchesProvider(): iterable
    {
        yield [false, null, '.', 'test', 'Failed asserting that null is a response object.'];
        yield [false, true, '.', false, 'Failed asserting that true is a response object.'];
        yield [false, '', '.', true, "Failed asserting that '' is a response object."];
        yield [false, new Response(), '.', null, 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            0,
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Empty response).',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            [],
            'Failed asserting that property "." (stdClass Object ()) is equal to Array ().',
        ];

        yield [
            false,
            new Response('[]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            null,
            'Failed asserting that property "." (Array ()) is equal to null.',
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            [(object) ['foo' => 'bar']],
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0]',
            (object) ['foo' => 'bar'],
        ];

        yield [
            false,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            [(object) ['foo' => 'bar']],
            'Failed asserting that property "." (stdClass Object (...)) is equal to Array (...).',
        ];

        yield [
            true,
            new Response('[{"foo":"bar","bar":true}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            [(object) ['foo' => 'bar','bar' => true]],
        ];

        yield [
            true,
            new Response('{"foo":["bar","bar"]}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            ['bar', 'bar'],
        ];

        yield [
            true,
            new Response('{"foo":{"bar":"bar"}}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            (object) ['bar' => 'bar'],
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            1,
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];

        yield [
            true,
            new Response('[{"foo":{"bar":{"bar":true}}}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0].foo.bar.bar',
            true,
        ];

        yield [
            false,
            new Response('[{"foo":{"bar":{"bar":true}}}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '[0].foo.foo.baz',
            true,
            'Error reading property "[0].foo.foo.baz" at path [0] -> foo -> foo [ERROR. Available keys: "bar"]'
        ];
    }

    public function testToString(): void
    {
        $constraint = new JsonResponsePropertyEquals('foo', 'test_foo');
        self::assertEquals('property "foo" is equal to \'test_foo\'', $constraint->toString());
    }
}
