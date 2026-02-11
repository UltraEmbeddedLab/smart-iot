<?php declare(strict_types=1);

namespace App\Services;

use App\Enums\TriggerOperator;
use App\Models\Trigger;

final class TriggerEvaluator
{
    /**
     * Evaluate whether the trigger condition is met for the given value.
     */
    public function evaluate(Trigger $trigger, mixed $currentValue): bool
    {
        $triggerValue = $trigger->value;

        if (is_numeric($currentValue) && is_numeric($triggerValue)) {
            return $this->compareNumeric((float) $currentValue, $trigger->operator, (float) $triggerValue);
        }

        return $this->compareString((string) $currentValue, $trigger->operator, (string) $triggerValue);
    }

    private function compareNumeric(float $current, TriggerOperator $operator, float $threshold): bool
    {
        return match ($operator) {
            TriggerOperator::Equals => $current === $threshold,
            TriggerOperator::NotEquals => $current !== $threshold,
            TriggerOperator::GreaterThan => $current > $threshold,
            TriggerOperator::LessThan => $current < $threshold,
            TriggerOperator::GreaterOrEqual => $current >= $threshold,
            TriggerOperator::LessOrEqual => $current <= $threshold,
        };
    }

    private function compareString(string $current, TriggerOperator $operator, string $threshold): bool
    {
        return match ($operator) {
            TriggerOperator::Equals => $current === $threshold,
            TriggerOperator::NotEquals => $current !== $threshold,
            TriggerOperator::GreaterThan => $current > $threshold,
            TriggerOperator::LessThan => $current < $threshold,
            TriggerOperator::GreaterOrEqual => $current >= $threshold,
            TriggerOperator::LessOrEqual => $current <= $threshold,
        };
    }
}
