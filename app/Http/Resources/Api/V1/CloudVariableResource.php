<?php declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Enums\VariableUpdatePolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CloudVariable */
final class CloudVariableResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'variable_name' => $this->variable_name,
            'type' => $this->type->value,
            'permission' => $this->permission->value,
            'update_policy' => $this->update_policy->value,
            'update_parameter' => $this->when(
                $this->update_policy === VariableUpdatePolicy::Periodically,
                $this->update_parameter,
            ),
        ];
    }
}
