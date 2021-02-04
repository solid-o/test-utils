<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseTest extends TestCase
{
    private JsonResponse $constraint;

    protected function setUp(): void
    {
        $this->constraint = new JsonResponse();
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($expected, $response, $message = ''): void
    {
        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($message);
        } else {
            $this->addToAssertionCount(1);
        }

        $this->constraint->evaluate($response);
    }

    public function matchesProvider(): iterable
    {
        yield [false, null, 'Failed asserting that null is a response object.'];
        yield [false, true, 'Failed asserting that true is a response object.'];
        yield [false, '', "Failed asserting that '' is a response object."];
        yield [false, new Response(), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Empty response).',
        ];

        yield [
            true,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
        ];

        yield [
            true,
            new Response('{"foo":"bar"}', Response::HTTP_OK, ['Content-Type' => 'application/json']),
        ];

        yield [
            false,
            new Response('{"foo":"bar"', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];
    }

    public function testToString(): void
    {
        self::assertEquals('is valid JSON response', $this->constraint->toString());
    }
}
