<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeviceHeartbeatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Device $device */
        $device = $request->attributes->get('device');
        $device->updateLastActivity();

        return response()->json([
            'status' => 'ok',
            'last_activity_at' => $device->last_activity_at->toIso8601String(),
        ]);
    }
}
