<?php

declare(strict_types=1);

require_once __DIR__ . '/ConcreteValidationWalker.php';

use Solido\QueryLanguage\Expression\AllExpression;
use Solido\QueryLanguage\Walker\Validation\ValidationWalkerInterface;
use Solido\TestUtils\QueryLanguage\AbstractValidationWalkerTestCase;

class SuccessTest extends AbstractValidationWalkerTestCase
{
    protected function createValidationWalker(): ValidationWalkerInterface
    {
        return new ConcreteValidationWalker();
    }

    public function testFoo(): void
    {
        (new AllExpression())->dispatch($this->walker);
        $this->assertNoViolation();
    }
}
