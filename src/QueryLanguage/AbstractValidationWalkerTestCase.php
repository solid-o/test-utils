<?php

declare(strict_types=1);

namespace Solido\TestUtils\QueryLanguage;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Solido\QueryLanguage\Walker\Validation\ValidationWalkerInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;
use function interface_exists;
use function Safe\sprintf;

abstract class AbstractValidationWalkerTestCase extends TestCase
{
    protected ExecutionContext $context;
    protected ValidationWalkerInterface $walker;

    abstract protected function createValidationWalker(): ValidationWalkerInterface;

    protected function setUp(): void
    {
        /** @codeCoverageIgnoreStart */
        if (! interface_exists(ValidatorInterface::class)) {
            throw new RuntimeException('Symfony Validator component is required to run validation walker tests. Try run composer require symfony/validator.');
        }

        if (! interface_exists(TranslatorInterface::class)) {
            throw new RuntimeException('Symfony Translator component is required to run validation walker tests. Try run composer require symfony/translation.');
        }

        /** @codeCoverageIgnoreEnd */

        $this->context = $this->createContext();
        $this->walker = $this->createValidationWalker();
        $this->walker->setValidationContext($this->context);
    }

    protected function createContext(): ExecutionContext
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $validator = $this->createMock(ValidatorInterface::class);

        return new ExecutionContext($validator, null, $translator);
    }

    protected function assertNoViolation(): void
    {
        self::assertSame(0, $violationsCount = count($this->context->getViolations()), sprintf('0 violation expected. Got %u.', $violationsCount));
    }

    protected function buildViolation(string $message): ConstraintViolationAssertion
    {
        $assertion = new ConstraintViolationAssertion($this->context, $message);
        $assertion->atPath('');
        $assertion->setInvalidValue(null);

        return $assertion;
    }
}
