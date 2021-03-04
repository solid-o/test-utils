<?php

declare(strict_types=1);

namespace Solido\TestUtils\Constraint;

use Exception;
use PHPUnit\Framework\Constraint\Constraint;

use function array_map;
use function count;
use function implode;
use function is_string;
use function Safe\sprintf;

class SecurityPolicyChecked extends Constraint
{
    /** @var array<string, mixed>[] */
    private array $checkedPolicies;

    /** @var array<string, mixed>[] */
    private array $remaining;

    /**
     * @param array<string, mixed> ...$checkedPolicies
     */
    public function __construct(array ...$checkedPolicies)
    {
        $this->checkedPolicies = $checkedPolicies;
        $this->remaining = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        $checkedPolicies = $this->checkedPolicies;
        foreach ($checkedPolicies as $value) {
            foreach ($other as $key => $policy) {
                if (is_string($policy)) {
                    if ($policy === $value['action']) {
                        unset($other[$key]);
                    }

                    continue;
                }

                /** @phpstan-ignore-next-line */
                throw new Exception('Not implemented');
            }
        }

        $this->remaining = $other;

        return count($other) === 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        $message = sprintf(
            'polic%s ha%s been checked: ',
            count($this->remaining) === 1 ? 'y' : 'ies',
            count($this->remaining) === 1 ? 's' : 've',
        );

        $message .= implode(', ', array_map('json_encode', $this->remaining));
        $message .= ' ha' . (count($this->remaining) === 1 ? 's' : 've') . ' not been checked';

        return $message;
    }

    public function toString(): string
    {
        return sprintf(
            'security polic%s has been checked',
            count($this->checkedPolicies) === 1 ? 'y' : 'ies'
        );
    }
}
