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
    private string $value;

    /**
     * Initializes token.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $argument
     */
    public function scoreArgument($argument)
    {
        return is_string($argument) &&
            /** @phpstan-ignore-next-line */
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
        /** @phpstan-ignore-next-line */
        return sprintf('matches("%s")', $this->value);
    }
}
