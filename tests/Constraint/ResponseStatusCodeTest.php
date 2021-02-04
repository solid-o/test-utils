<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseStatusCode;
use Symfony\Component\HttpFoundation\Response;

class ResponseStatusCodeTest extends TestCase
{
    /**
     * @dataProvider providerMatches
     */
    public function testMatches(bool $expected, $codes, $response, $message = ''): void
    {
        $constraint = new ResponseStatusCode(...$codes);
        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($message);
        } else {
            $this->addToAssertionCount(1);
        }

        $constraint->evaluate($response);
    }

    public function providerMatches(): iterable
    {
        yield [false, [200], null, 'Failed asserting that null is a response object.'];
        yield [false, [200], new Response('', 204), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) status code (204 No Content) is equal to 200.'];
        yield [false, [200, 500], new Response('', 204), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) status code (204 No Content) is in (200, 500).'];
        yield [true, [200], new Response('', 200)];
        yield [true, [200, 500], new Response()];
        yield [true, [200, 500], new Response('', 500)];
    }

    public function testToString(): void
    {
        $constraint = new ResponseStatusCode(200);
        self::assertEquals('status code is equal to 200', $constraint->toString());

        $constraint = new ResponseStatusCode(200, 400);
        self::assertEquals('status code is in (200, 400)', $constraint->toString());
    }
}
