<?php

declare(strict_types=1);

use Solido\QueryLanguage\Expression\ValueExpression;
use Solido\QueryLanguage\Walker\Validation\ValidationWalker;

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
