<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\SecurityPolicyChecked;

class SecurityPolicyCheckedTest extends TestCase
{
    #[DataProvider('matchesProvider')]
    public function testSecurityPolicyMatches(bool $expected, array $checked, string $message = ''): void
    {
        $constraint = new SecurityPolicyChecked(
            ['action' => 'Get'],
            ['action' => 'Create'],
            ['action' => 'Edit'],
            ['action' => 'Delete'],
        );

        try {
            $constraint->evaluate($checked);
            self::assertTrue($expected);
        } catch (ExpectationFailedException $e) {
            self::assertFalse($expected);
            self::assertEquals($message, $e->getMessage());
        }
    }

    public static function matchesProvider(): iterable
    {
        yield [false, ['NoGet'], 'Failed asserting that policy has been checked: "NoGet" has not been checked.'];
        yield [false, ['NoGet', 'NoPut'], 'Failed asserting that policies have been checked: "NoGet", "NoPut" have not been checked.'];
        yield [false, ['NoDelete', 'Get', 'Edit', 'NoPut'], 'Failed asserting that policies have been checked: "NoDelete", "NoPut" have not been checked.'];
        yield [true, ['Get']];
        yield [true, ['Get', 'Edit']];
    }

    public function testToString(): void
    {
        $constraint = new SecurityPolicyChecked(['action' => 'Permission1']);
        self::assertEquals('security policy has been checked', $constraint->toString());

        $constraint = new SecurityPolicyChecked(['action' => 'Permission1'], ['action' => 'Permission2']);
        self::assertEquals('security policies has been checked', $constraint->toString());
    }
}
