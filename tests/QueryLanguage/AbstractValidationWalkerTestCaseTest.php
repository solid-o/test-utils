<?php

declare(strict_types=1);

namespace Solido\TestUtils\Tests\QueryLanguage;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use Solido\QueryLanguage\Expression\AllExpression;
use Solido\QueryLanguage\Expression\Comparison\LessThanExpression;
use Solido\QueryLanguage\Expression\Literal\LiteralExpression;
use Solido\QueryLanguage\Expression\ValueExpression;
use Solido\QueryLanguage\Walker\Validation\ValidationWalker;
use Solido\QueryLanguage\Walker\Validation\ValidationWalkerInterface;
use Solido\TestUtils\QueryLanguage\AbstractValidationWalkerTestCase;

class AbstractValidationWalkerTestCaseTest extends TestCase
{
    public function testSuccess(): void
    {
        $test = new SuccessTestCase();
        $result = $test->run();

        self::assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
        self::assertEquals(0, $result->errorCount());
        self::assertEquals(0, $result->failureCount());
        self::assertEquals(0, $result->skippedCount());
        self::assertCount(1, $result);
    }

    public function testFailure(): void
    {
        $test = new FailureTestCase();
        $result = $test->run();

        self::assertEquals(BaseTestRunner::STATUS_FAILURE, $test->getStatus());
        self::assertEquals(0, $result->errorCount());
        self::assertEquals(1, $result->failureCount());
        self::assertEquals(0, $result->skippedCount());
        self::assertCount(1, $result);
    }

    public function testViolationRaised(): void
    {
        $test = new ViolationRaisedTestCase();
        $test->run();

        self::assertEquals(BaseTestRunner::STATUS_PASSED, $test->getStatus());
    }
}

class ConcreteValidationWalker extends ValidationWalker
{
    public function walkComparison(string $operator, ValueExpression $expression): mixed
    {
        if ($operator !== '=') {
            $this->addViolation('Cannot be equal', ['invalid_value' => (string) $expression]);
        }

        return parent::walkComparison($operator, $expression);
    }
}

class SuccessTestCase extends AbstractValidationWalkerTestCase
{
    protected function createValidationWalker(): ValidationWalkerInterface
    {
        return new ConcreteValidationWalker();
    }

    public function runTest(): void
    {
        (new AllExpression())->dispatch($this->walker);
        $this->assertNoViolation();
    }
}

class FailureTestCase extends AbstractValidationWalkerTestCase
{
    protected function createValidationWalker(): ValidationWalkerInterface
    {
        return new ConcreteValidationWalker();
    }

    public function runTest(): void
    {
        (new LessThanExpression(LiteralExpression::create('42')))->dispatch($this->walker);
        $this->assertNoViolation();
    }
}

class ViolationRaisedTestCase extends AbstractValidationWalkerTestCase
{
    protected function createValidationWalker(): ValidationWalkerInterface
    {
        return new ConcreteValidationWalker();
    }

    public function runTest(): void
    {
        (new LessThanExpression(LiteralExpression::create('42')))->dispatch($this->walker);

        $this->buildViolation('Cannot be equal')
            ->setParameters(['invalid_value' => '42'])
            ->assertRaised();
    }
}
