<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\JsonResponse;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

use function json_last_error;

use const JSON_ERROR_NONE;

class JsonResponseTest extends TestCase
{
    private JsonResponse $constraint;

    protected function setUp(): void
    {
        $this->constraint = new JsonResponse();
    }

    #[DataProvider('matchesProvider')]
    public function testMatches($expected, $response, $message = ''): void
    {
        if ($expected) {
            $this->addToAssertionCount(1);
        }

        try {
            $this->constraint->evaluate($response);
            self::assertTrue($expected);
        } catch (ExpectationFailedException $e) {
            self::assertFalse($expected);
            self::assertEquals($message, $e->getMessage());
        } finally {
            self::assertEquals(JSON_ERROR_NONE, json_last_error());
        }
    }

    public static function matchesProvider(): iterable
    {
        yield [false, null, 'Failed asserting that null is a response object.'];
        yield [false, true, 'Failed asserting that true is a response object.'];
        yield [false, '', "Failed asserting that '' is a response object."];
        yield [false, new stdClass(), 'Failed asserting that stdClass Object () is a response object.'];
        yield [false, new Response(), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.'];
        yield [
            false,
            new Response('', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Empty response).',
        ];

        yield [
            false,
            new Response('{ test: foo', Response::HTTP_OK, ['Content-Type' => 'application/json']),
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is valid JSON response (Syntax error, malformed JSON).',
        ];

        yield [
            false,
            new Response('{}', Response::HTTP_OK, ['Content-Type' => 'application/not-a-json']),
            'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has json content type.',
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
