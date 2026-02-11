<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProvisionDeviceRequest;
use App\Http\Resources\Api\V1\ProvisioningResource;
use App\Models\Device;
use App\Services\MqttProvisioningService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class DeviceProvisionController extends Controller
{
    public function __invoke(
        ProvisionDeviceRequest $request,
        MqttProvisioningService $mqttService,
    ): ProvisioningResource|JsonResponse {
        $device = Device::query()->where('device_id', $request->validated('device_id'))->first();

        if (! $device || ! $device->verifySecretKey($request->validated('secret_key'))) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $device->load('thing.cloudVariables');
        $device->markAsOnline();

        $mqttConfig = $mqttService->generateMqttConfig($device);
        $topics = $mqttService->generateTopics($device);

        return (new ProvisioningResource($device))->withMqtt($mqttConfig, $topics);
    }
}
