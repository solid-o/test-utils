<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseHeaderSame;
use Symfony\Component\HttpFoundation\Response;

class ResponseHeaderSameTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches($expected, string $name, string $value, $response, string $message = ''): void
    {
        $constraint = new ResponseHeaderSame($name, $value);
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
        yield [false, 'Accept', '', null, 'Failed asserting that null is a response object.'];
        yield [false, 'Accept', '', true, 'Failed asserting that true is a response object.'];
        yield [false, 'Accept', '', '', "Failed asserting that '' is a response object."];
        yield [false, 'Content-Length', '30', new Response(), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has header "Content-Length" with value "30" (header missing).'];
        yield [false, 'Content-Encoding', '30', new Response('', 200, ['Content-Encoding' => 'gzip']), 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) has header "Content-Encoding" with value "30" (actual value: "gzip").'];
        yield [true, 'Content-Length', '7442', new Response('', 200, ['Content-Length' => '7442'])];
    }

    public function testToString(): void
    {
        $constraint = new ResponseHeaderSame('Content-Type', '42');
        self::assertEquals('has header "Content-Type" with value "42"', $constraint->toString());
    }
}
