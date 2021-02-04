<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseIsRedirection;
use Symfony\Component\HttpFoundation\Response;

class ResponseIsRedirectionTest extends TestCase
{
    /**
     * @dataProvider providerMatches
     */
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

    public function providerMatches(): iterable
    {
        yield [false, null, 'Failed asserting that null is a response object.'];
        yield [false, new Response('', 500), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is redirection.'];
        yield [false, new Response('', 200), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is redirection.'];
        yield [true, new Response('', 301)];
    }

    public function testToString(): void
    {
        $constraint = new ResponseIsRedirection();
        self::assertEquals('is redirection', $constraint->toString());
    }
}
