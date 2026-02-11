<?php declare(strict_types=1);

namespace App\Jobs;

use App\Models\CloudVariable;
use App\Models\Thing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ProcessMqttMessage implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $thingUuid,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $thing = Thing::query()
            ->where('uuid', $this->thingUuid)
            ->with('cloudVariables', 'device')
            ->first();

        if ($thing === null) {
            Log::warning('ProcessMqttMessage: Thing not found', ['uuid' => $this->thingUuid]);

            return;
        }

        /** @var \Illuminate\Support\Collection<string, CloudVariable> $variablesByName */
        $variablesByName = $thing->cloudVariables->keyBy('variable_name');

        foreach ($this->payload as $variableName => $value) {
            /** @var CloudVariable|null $variable */
            $variable = $variablesByName->get($variableName);

            if ($variable === null) {
                Log::debug('ProcessMqttMessage: Unknown variable', [
                    'thing_uuid' => $this->thingUuid,
                    'variable_name' => $variableName,
                ]);

                continue;
            }

            $variable->updateValue($value);
        }

        $thing->device?->updateLastActivity();
    }
}
