<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\Prophecy\Argument\Token;

use PHPUnit\Framework\TestCase;
use Solido\TestUtils\Prophecy\Argument\Token\StringMatchesToken;

class StringMatchesTokenTest extends TestCase
{
    private StringMatchesToken $token;

    protected function setUp(): void
    {
        $this->token = new StringMatchesToken('/a regex (pattern|substring)/');
    }

    public function testGetValueShouldReturnThePattern(): void
    {
        self::assertEquals('/a regex (pattern|substring)/', $this->token->getValue());
    }

    public function testIsNotLast(): void
    {
        self::assertFalse($this->token->isLast());
    }

    public function testShouldScore6IfArgumentMatches(): void
    {
        self::assertEquals(6, $this->token->scoreArgument('this matches a regex pattern defined'));
        self::assertEquals(6, $this->token->scoreArgument('this matches a regex substring defined'));
    }

    public function testShouldScoreFalseIfArgumentDoesNotMatch(): void
    {
        self::assertFalse($this->token->scoreArgument('Argument will not match'));
    }

    public function testShouldScoreFalseIfArgumentIsNotAString(): void
    {
        self::assertFalse($this->token->scoreArgument(['a regex pattern', 'Argument will not match']));
    }

    public function testToString(): void
    {
        self::assertEquals('matches("/a regex (pattern|substring)/")', (string) $this->token);
    }
}
