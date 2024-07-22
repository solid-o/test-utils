<?php

declare(strict_types=1);

require_once __DIR__ . '/ConcreteValidationWalker.php';

use Solido\QueryLanguage\Expression\Comparison\LessThanExpression;
use Solido\QueryLanguage\Expression\Literal\LiteralExpression;
use Solido\QueryLanguage\Walker\Validation\ValidationWalkerInterface;
use Solido\TestUtils\QueryLanguage\AbstractValidationWalkerTestCase;

class FailureTest extends AbstractValidationWalkerTestCase
{
    protected function createValidationWalker(): ValidationWalkerInterface
    {
        return new ConcreteValidationWalker();
    }

    public function testFoo(): void
    {
        (new LessThanExpression(LiteralExpression::create('42')))->dispatch($this->walker);
        $this->assertNoViolation();
    }
}
