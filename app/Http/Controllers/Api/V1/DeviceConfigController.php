<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DeviceConfigResource;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeviceConfigController extends Controller
{
    public function __invoke(Request $request, Device $device): DeviceConfigResource|JsonResponse
    {
        /** @var Device $authenticatedDevice */
        $authenticatedDevice = $request->attributes->get('device');

        if ($authenticatedDevice->id !== $device->id) {
            return response()->json([
                'message' => 'You may only access your own device configuration.',
            ], Response::HTTP_FORBIDDEN);
        }

        $device->load('thing.cloudVariables');

        return new DeviceConfigResource($device);
    }
}
