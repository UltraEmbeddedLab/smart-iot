<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TriggerActionType;
use App\Mail\TriggerAlertMail;
use App\Models\Trigger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class ExecuteTriggerAction implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public Trigger $trigger,
    ) {}

    public function handle(): void
    {
        match ($this->trigger->action_type) {
            TriggerActionType::Email => $this->sendEmail(),
            TriggerActionType::Webhook => $this->fireWebhook(),
            default => Log::warning('Unimplemented trigger action type', [
                'trigger_id' => $this->trigger->id,
                'action_type' => $this->trigger->action_type->value,
            ]),
        };
    }

    private function sendEmail(): void
    {
        $email = $this->trigger->action_config['email'] ?? null;

        if (! $email) {
            Log::error('Trigger email action missing email address', ['trigger_id' => $this->trigger->id]);

            return;
        }

        Mail::to($email)->send(new TriggerAlertMail($this->trigger));
    }

    private function fireWebhook(): void
    {
        $url = $this->trigger->action_config['url'] ?? null;

        if (! $url) {
            Log::error('Trigger webhook action missing URL', ['trigger_id' => $this->trigger->id]);

            return;
        }

        $this->trigger->loadMissing('cloudVariable.thing');

        $cloudVariable = $this->trigger->cloudVariable;

        Http::timeout(10)->post($url, [
            'trigger' => [
                'id' => $this->trigger->uuid,
                'name' => $this->trigger->name,
            ],
            'variable' => [
                'name' => $cloudVariable->name,
                'value' => $cloudVariable->last_value,
            ],
            'condition' => [
                'operator' => $this->trigger->operator->symbol(),
                'threshold' => $this->trigger->value,
            ],
            'triggered_at' => $this->trigger->last_triggered_at?->toIso8601String(),
        ]);
    }
}
