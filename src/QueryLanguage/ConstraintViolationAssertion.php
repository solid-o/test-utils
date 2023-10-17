<?php

declare(strict_types=1);

namespace Solido\TestUtils\QueryLanguage;

use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use function count;
use function current;
use function iterator_to_array;
use function next;
use function reset;
use function sprintf;

/** @internal */
class ConstraintViolationAssertion // phpcs:ignore
{
    /** @var array<string, mixed> */
    private array $parameters = [];
    private mixed $invalidValue = 'InvalidValue';
    private string $propertyPath = 'property.path';
    private int|null $plural = null;
    private string|null $code = null;
    private mixed $cause = null;

    /** @param ConstraintViolationAssertion[] $assertions */
    public function __construct(
        private readonly ExecutionContextInterface $context,
        private readonly string $message,
        private readonly Constraint|null $constraint = null,
        private readonly array $assertions = [],
    ) {
    }

    public function atPath(string $path): self
    {
        $this->propertyPath = $path;

        return $this;
    }

    public function setParameter(string $key, string $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /** @param array<string, mixed> $parameters */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function setInvalidValue(mixed $invalidValue): self
    {
        $this->invalidValue = $invalidValue;

        return $this;
    }

    public function setPlural(int $number): self
    {
        $this->plural = $number;

        return $this;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setCause(mixed $cause): self
    {
        $this->cause = $cause;

        return $this;
    }

    public function buildNextViolation(string $message): self
    {
        $assertions = $this->assertions;
        $assertions[] = $this;

        return new self($this->context, $message, $this->constraint, $assertions);
    }

    public function assertRaised(): void
    {
        $expected = [];
        foreach ($this->assertions as $assertion) {
            $expected[] = $assertion->getViolation();
        }

        $expected[] = $this->getViolation();

        $violations = iterator_to_array($this->context->getViolations());

        Assert::assertSame($expectedCount = count($expected), $violationsCount = count($violations), sprintf('%u violation(s) expected. Got %u.', $expectedCount, $violationsCount));

        reset($violations);

        foreach ($expected as $violation) {
            Assert::assertEquals($violation, current($violations));
            next($violations);
        }
    }

    private function getViolation(): ConstraintViolation
    {
        return new ConstraintViolation(
            $this->message,
            $this->message,
            $this->parameters,
            $this->context->getRoot(),
            $this->propertyPath,
            $this->invalidValue,
            $this->plural,
            $this->code,
            $this->constraint,
            $this->cause,
        );
    }
}
