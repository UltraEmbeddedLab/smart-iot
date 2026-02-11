<?php declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Device */
final class DeviceConfigResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Device $device */
        $device = $this->resource;
        $thing = $device->thing;

        return [
            'device_id' => $device->device_id,
            'status' => $device->status->value,
            'thing_id' => $thing?->uuid,
            'variables' => CloudVariableResource::collection(
                $thing !== null ? $thing->cloudVariables : collect(),
            ),
        ];
    }
}
