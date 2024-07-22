<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponsePropertiesExist;
use Symfony\Component\HttpFoundation\Response;

class JsonResponsePropertiesExistTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches($expected, $response, array $paths, $message = ''): void
    {
        $constraint = new JsonResponsePropertiesExist($paths);

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
        yield [false, null, ['foo'], 'Failed asserting that null is a response object.'];
        yield [false, true, ['foo'], 'Failed asserting that true is a response object.'];
        yield [false, '', ['foo'], "Failed asserting that '' is a response object."];
        yield [false, new Response(), ['foo'], 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo'],
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Empty response).',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo'],
            'Failed asserting that property "foo" exists.',
        ];

        yield [
            true,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo'],
        ];

        yield [
            false,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo', 'bar'],
            'Failed asserting that property "bar" exists.',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo', 'bar'],
            'Failed asserting that properties "foo", "bar" exist.',
        ];

        yield [
            true,
            new Response('{"foo":"bar","bar":true}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo', 'bar'],
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            ['foo'],
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];
    }

    public function testToString(): void
    {
        $constraint = new JsonResponsePropertiesExist(['foo']);
        self::assertEquals('property "foo" exists', $constraint->toString());

        $constraint = new JsonResponsePropertiesExist(['foo', 'bar']);
        self::assertEquals('properties "foo", "bar" exist', $constraint->toString());
    }
}
