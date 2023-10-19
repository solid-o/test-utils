<?php

declare(strict_types=1);

namespace Solido\TestUtils\Prophecy\Argument\Token;

use Prophecy\Argument\Token\TokenInterface;

use function is_string;
use function preg_match;
use function sprintf;

/**
 * String contains token.
 */
class StringMatchesToken implements TokenInterface
{
    /**
     * Initializes token.
     */
    public function __construct(private readonly string $value)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $argument
     */
    public function scoreArgument($argument)
    {
        return is_string($argument) &&
            preg_match($this->value, $argument) === 1 ? 6 : false;
    }

    /**
     * Returns preset value against which token checks arguments.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Returns false.
     */
    public function isLast(): bool
    {
        return false;
    }

    /**
     * Returns string representation for token.
     */
    public function __toString(): string
    {
        return sprintf('matches("%s")', $this->value);
    }
}
