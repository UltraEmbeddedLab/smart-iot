<?php declare(strict_types=1);

namespace App\Listeners;

use App\Events\CloudVariableUpdated;
use App\Jobs\ExecuteTriggerAction;
use App\Models\Trigger;
use App\Services\TriggerEvaluator;

final class EvaluateCloudTriggers
{
    public function __construct(
        private TriggerEvaluator $evaluator,
    ) {}

    public function handle(CloudVariableUpdated $event): void
    {
        $triggers = Trigger::query()
            ->where('cloud_variable_id', $event->cloudVariable->id)
            ->where('is_active', true)
            ->get();

        $currentValue = $event->newValue['value'] ?? null;

        if ($currentValue === null) {
            return;
        }

        foreach ($triggers as $trigger) {
            if (! $this->evaluator->evaluate($trigger, $currentValue)) {
                continue;
            }

            if ($trigger->isOnCooldown()) {
                continue;
            }

            $trigger->update(['last_triggered_at' => now()]);

            ExecuteTriggerAction::dispatch($trigger);
        }
    }
}
