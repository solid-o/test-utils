<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponsePropertyCount;
use Symfony\Component\HttpFoundation\Response;

class JsonResponsePropertyCountTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches($expected, $response, $path, int $count, $message = ''): void
    {
        $constraint = new JsonResponsePropertyCount($path, $count);

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
        yield [false, null, '.', 0, 'Failed asserting that null is a response object.'];
        yield [false, true, '.', 0, 'Failed asserting that true is a response object.'];
        yield [false, '', '.', 0, "Failed asserting that '' is a response object."];
        yield [false, new Response(), '.', 0, 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
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
            0,
            'Failed asserting that property "." count matches 0.',
        ];

        yield [
            false,
            new Response('[]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            1,
            'Failed asserting that property "." count matches 1.',
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            1,
        ];

        yield [
            false,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            1,
            'Failed asserting that property "." count matches 1.',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            0,
            'Failed asserting that property "." count matches 0.',
        ];

        yield [
            true,
            new Response('[{"foo":"bar","bar":true}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            1,
        ];

        yield [
            true,
            new Response('{"foo":["bar","bar"]}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            2,
        ];

        yield [
            false,
            new Response('{"foo":{"bar":"bar"}}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            2,
            'Failed asserting that property "foo" count matches 2.',
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            1,
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];
    }

    public function testToString(): void
    {
        $constraint = new JsonResponsePropertyCount('foo', 42);
        self::assertEquals('property "foo" count matches 42', $constraint->toString());
    }
}
