<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->header('X-Device-ID');
        $secretKey = $request->header('X-Secret-Key');

        if (! $deviceId || ! $secretKey) {
            return response()->json([
                'message' => 'Device authentication required.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $device = Device::query()->where('device_id', $deviceId)->first();

        if (! $device || ! $device->verifySecretKey($secretKey)) {
            return response()->json([
                'message' => 'Invalid device credentials.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('device', $device);

        return $next($request);
    }
}
