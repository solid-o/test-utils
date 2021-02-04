<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Constraint\SecurityPolicyChecked;

class SecurityPolicyCheckedTest extends TestCase
{
    /**
     * @dataProvider provideMatches
     */
    public function testMatches(bool $expected, array $checked, string $message = ''): void
    {
        $constraint = new SecurityPolicyChecked(
            ['action' => 'Get'],
            ['action' => 'Create'],
            ['action' => 'Edit'],
            ['action' => 'Delete'],
        );

        if (! $expected) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($message);
        } else {
            $this->addToAssertionCount(1);
        }

        $constraint->evaluate($checked);
    }

    public function provideMatches(): iterable
    {
        yield [false, ['NoGet'], 'Failed asserting that policy has been checked: "NoGet" has not been checked.'];
        yield [false, ['NoGet', 'NoPut'], 'Failed asserting that policies have been checked: "NoGet", "NoPut" have not been checked.'];
        yield [true, ['Get']];
        yield [true, ['Get', 'Edit']];
    }

    public function testToString(): void
    {
        $constraint = new SecurityPolicyChecked('Permission1');
        self::assertEquals('security policy has been checked', $constraint->toString());

        $constraint = new SecurityPolicyChecked('Permission1', 'Permission2');
        self::assertEquals('security policies has been checked', $constraint->toString());
    }
}
