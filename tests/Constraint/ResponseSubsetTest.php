<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\ResponseSubset;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseSubsetTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testMatches($expected, $subset, $response, string $message = ''): void
    {
        $constraint = new ResponseSubset($subset);
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
        yield 'array subset and json response, strict not matching' => [
            'expected' => false,
            'subset' => ['bar' => 0],
            'response' => new JsonResponse(['foo' => '', 'bar' => '0']),
            'message' => 'Failed asserting that Symfony\Component\HttpFoundation\JsonResponse Object (...) contains subset Array &0 [
    \'bar\' => 0,
].',
        ];

        yield 'array subset and json response, matching' => [
            'expected' => true,
            'subset' => ['bar' => '0'],
            'response' => new JsonResponse(['foo' => '', 'bar' => '0']),
            'message' => '',
        ];

        yield 'array subset and json response, matching recursive' => [
            'expected' => true,
            'subset' => ['bar' => ['barbar' => '1']],
            'response' => new JsonResponse(['foo' => '', 'bar' => ['foobar' => '0', 'barbar' => '1']]),
            'message' => '',
        ];

        yield 'complex array subset and json response, matching recursive' => [
            'expected' => true,
            'subset' => ['foo' => '', 'bar' => ['foobar' => '0', 'barbar' => '1']],
            'response' => new JsonResponse(['foo' => '', 'baz' => 2, 'bar' => ['foobar' => '0', 'barbar' => '1']]),
            'message' => '',
        ];

        yield 'complex array subset and json response, matching recursive equals' => [
            'expected' => true,
            'subset' => ['foo' => '', 'baz' => 2, 'bar' => ['foobar' => '0', 'barbar' => '1']],
            'response' => new JsonResponse(['foo' => '', 'baz' => 2, 'bar' => ['foobar' => '0', 'barbar' => '1']]),
            'message' => '',
        ];

        yield 'complex array subset and json response, not matching recursive equals' => [
            'expected' => false,
            'subset' => ['foo' => '', 'baz' => 2, 'bar' => ['foobar' => '0', 'barbar' => '1']],
            'response' => new JsonResponse(['foo' => '', 'bar' => ['foobar' => '0', 'barbar' => '1']]),
            'message' => <<<'ERR'
            Failed asserting that Symfony\Component\HttpFoundation\JsonResponse Object (...) contains subset Array &0 [
                'foo' => '',
                'baz' => 2,
                'bar' => Array &1 [
                    'foobar' => '0',
                    'barbar' => '1',
                ],
            ]. Actual response content is: Array &0 [
                'foo' => '',
                'bar' => Array &1 [
                    'foobar' => '0',
                    'barbar' => '1',
                ],
            ].
            ERR,
        ];

        yield 'string subset and string response, not matching' => [
            'expected' => false,
            'subset' => 'great jupiter!',
            'response' => new Response('This is a great foo day!'),
            'message' => 'Failed asserting that Symfony\Component\HttpFoundation\Response Object (...) contains subset \'great jupiter!\'.',
        ];

        yield 'string subset and string response, matching' => [
            'expected' => true,
            'subset' => 'great foo',
            'response' => new Response('This is a great foo day!'),
            'message' => '',
        ];
    }

    public function testToString(): void
    {
        $constraint = new ResponseSubset(['bar' => 0]);
        self::assertEquals("contains subset Array &0 [\n    'bar' => 0,\n]", $constraint->toString());
    }
}
