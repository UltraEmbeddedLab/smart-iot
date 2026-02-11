<?php declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Device */
final class ProvisioningResource extends JsonResource
{
    /** @var array<string, mixed>|null */
    private ?array $mqttConfig = null;

    /** @var array<string, string>|null */
    private ?array $topics = null;

    /**
     * @param  array<string, mixed>  $mqtt
     * @param  array<string, string>  $topics
     */
    public function withMqtt(array $mqtt, array $topics): self
    {
        $this->mqttConfig = $mqtt;
        $this->topics = $topics;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Device $device */
        $device = $this->resource;
        $thing = $device->thing;

        return [
            'status' => 'provisioned',
            'device_id' => $device->device_id,
            'thing_id' => $thing?->uuid,
            'mqtt' => $this->mqttConfig,
            'topics' => $this->topics,
            'variables' => CloudVariableResource::collection(
                $thing !== null ? $thing->cloudVariables : collect(),
            ),
        ];
    }
}
