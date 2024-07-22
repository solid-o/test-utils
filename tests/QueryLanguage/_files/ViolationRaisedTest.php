<?php

declare(strict_types=1);

require_once __DIR__ . '/ConcreteValidationWalker.php';

use Solido\QueryLanguage\Expression\Comparison\LessThanExpression;
use Solido\QueryLanguage\Expression\Literal\LiteralExpression;
use Solido\QueryLanguage\Walker\Validation\ValidationWalkerInterface;
use Solido\TestUtils\QueryLanguage\AbstractValidationWalkerTestCase;

class ViolationRaisedTest extends AbstractValidationWalkerTestCase
{
    protected function createValidationWalker(): ValidationWalkerInterface
    {
        return new ConcreteValidationWalker();
    }

    public function testFoo(): void
    {
        (new LessThanExpression(LiteralExpression::create('42')))->dispatch($this->walker);

        $this->buildViolation('Cannot be equal')
             ->setParameters(['invalid_value' => '42'])
             ->assertRaised();
    }
}
