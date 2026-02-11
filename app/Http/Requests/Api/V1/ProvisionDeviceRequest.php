<?php declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class ProvisionDeviceRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'uuid'],
            'secret_key' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_id.required' => 'A device ID is required for provisioning.',
            'device_id.uuid' => 'The device ID must be a valid UUID.',
            'secret_key.required' => 'A secret key is required for provisioning.',
            'secret_key.min' => 'The secret key must be at least 8 characters.',
        ];
    }
}
