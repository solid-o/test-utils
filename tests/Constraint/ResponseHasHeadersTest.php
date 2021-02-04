<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseHasHeaders;
use Symfony\Component\HttpFoundation\Response;

class ResponseHasHeadersTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testMatches($expected, array $headers, $response, string $message = ''): void
    {
        $constraint = new ResponseHasHeaders($headers);
        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($message);
        } else {
            $this->addToAssertionCount(1);
        }

        $constraint->evaluate($response);
    }

    public function matchesProvider(): iterable
    {
        yield [false, [], null, 'Failed asserting that null is a response object.'];
        yield [false, [], true, 'Failed asserting that true is a response object.'];
        yield [false, [], '', "Failed asserting that '' is a response object."];
        yield [false, ['Content-Length'], new Response(), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has "Content-Length" header.'];
        yield [false, ['Content-Encoding', 'X-API-Version'], new Response(), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has "Content-Encoding", "X-API-Version" headers.'];
        yield [true, ['Content-Length'], new Response('', 200, ['Content-Length' => '7442'])];
        yield [false, ['Content-Encoding', 'X-API-Version'], new Response('', 200, ['Content-Encoding' => 'gzip']), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has "X-API-Version" header.'];
        yield [false, ['Content-Encoding', 'X-API-Version'], new Response('', 200, ['Content-Type' => 'application/json']), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has "Content-Encoding", "X-API-Version" headers.'];
        yield [true, ['Content-Encoding', 'X-API-Version'], new Response('', 200, ['Content-Encoding' => 'gzip', 'X-API-Version' => '2.0'])];
    }

    public function testToString(): void
    {
        $constraint = new ResponseHasHeaders(['Content-Type']);
        self::assertEquals('has "Content-Type" header', $constraint->toString());

        $constraint = new ResponseHasHeaders(['X-Total-Count', 'X-Continuation-Token']);
        self::assertEquals('has "X-Total-Count", "X-Continuation-Token" headers', $constraint->toString());
    }
}
