<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponsePropertyContains;
use Symfony\Component\HttpFoundation\Response;

class JsonResponsePropertyContainsTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($expected, $response, $path, $properties, $message = ''): void
    {
        $constraint = new JsonResponsePropertyContains($path, $properties);

        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessageMatches('/^' . $message . '$/');
        } else {
            $this->addToAssertionCount(1);
        }

        $constraint->evaluate($response);
    }

    public function matchesProvider(): iterable
    {
        yield [false, null, '.', ['foo'], 'Failed asserting that null is a response object.'];
        yield [false, true, '.', ['foo'], 'Failed asserting that true is a response object.'];
        yield [false, '', '.', ['foo'], "Failed asserting that '' is a response object."];
        yield [false, new Response(), '.', ['foo'], 'Failed asserting that Symfony\\\\Component\\\\HttpFoundation\\\\Response Object \(\.\.\.\) has json content type\.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['foo'],
            'Failed asserting that Symfony\\\\Component\\\\HttpFoundation\\\\Response Object \(\.\.\.\) is valid JSON response \(Empty response\)\.',
        ];

        yield [
            false,
            new Response('false', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            'Property "\." is not an array \(bool\)',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            'Failed asserting that property "\." \(stdClass Object &.+ \(\)\) contains \'foo\'\.',
        ];

        yield [
            false,
            new Response('[]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            'foo',
            <<<ERR
            Failed asserting that property "\." \(Array &0 \(\)\) contains 'foo'\.
            ERR,
        ];

        yield [
            true,
            new Response('[{"foo":"bar"}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['foo' => 'bar'],
        ];

        yield [
            false,
            new Response('["42"]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            42,
            <<<'ERR'
            Failed asserting that property "\." \(Array &0 \(
                0 => '42'
            \)\) contains 42\.
            ERR];

        yield [
            false,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['foo' => 'foo', 'bar' => 'bar'],
            <<<ERR
            Failed asserting that property "\." \(stdClass Object &.+ \(
                'foo' => 'bar'
            \)\) contains stdClass Object &.+ \(
                'foo' => 'foo'
                'bar' => 'bar'
            \).
            ERR,
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['foo', 'bar'],
            <<<ERR
            Failed asserting that property "\." \(stdClass Object &.+ \(\)\) contains Array &0 \(
                0 => 'foo'
                1 => 'bar'
            \).
            ERR,
        ];

        yield [
            true,
            new Response('[{"foo":"bar","bar":true}]', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['foo' => 'bar', 'bar' => true],
        ];

        yield [
            true,
            new Response('{"foo":["bar","bar"]}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'foo',
            'bar',
        ];

        yield [
            true,
            new Response('{"foo":{"bar":"bar"}}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['bar' => 'bar'],
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            '.',
            ['foo'],
            'Failed asserting that Symfony\\\\Component\\\\HttpFoundation\\\\Response Object \(\.\.\.\) is valid JSON response \(Syntax error, malformed JSON\)\.',
        ];
    }

    public function testToString(): void
    {
        $constraint = new JsonResponsePropertyContains('foo', ['foo']);
        self::assertEquals('property "foo" contains Array (...)', $constraint->toString());

        $constraint = new JsonResponsePropertyContains('foo', ['foo', 'bar']);
        self::assertEquals('property "foo" contains Array (...)', $constraint->toString());
    }
}
