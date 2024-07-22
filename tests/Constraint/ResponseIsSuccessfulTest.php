<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseIsSuccessful;
use Symfony\Component\HttpFoundation\Response;

class ResponseIsSuccessfulTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches(bool $expected, $response, $message = ''): void
    {
        $constraint = new ResponseIsSuccessful();
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
        yield [false, new Response('', 500), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is successful.'];
        yield [false, new Response('', 300), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) is successful.'];
        yield [true, new Response('', 200)];
    }

    public function testToString(): void
    {
        $constraint = new ResponseIsSuccessful();
        self::assertEquals('is successful', $constraint->toString());
    }
}
