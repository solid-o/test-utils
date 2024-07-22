<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseIsRedirection;
use Symfony\Component\HttpFoundation\Response;

class ResponseIsRedirectionTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches(bool $expected, $response, $message = ''): void
    {
        $constraint = new ResponseIsRedirection();
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
        yield [false, null, 'Failed asserting that null is a response object.'];
        yield [false, new Response('', 500), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is redirection.'];
        yield [false, new Response('', 200), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is redirection.'];
        yield [true, new Response('', 300)];
        yield [true, new Response('', 301)];
        yield [true, new Response('', 302)];
        yield [true, new Response('', 303)];
        yield [true, new Response('', 307)];
        yield [true, new Response('', 308)];
    }

    public function testToString(): void
    {
        $constraint = new ResponseIsRedirection();
        self::assertEquals('is redirection', $constraint->toString());
    }
}
