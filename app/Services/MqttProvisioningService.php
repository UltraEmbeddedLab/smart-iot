<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Str;

final class MqttProvisioningService
{
    /**
     * Generate MQTT connection config for a device.
     *
     * @return array{broker: string, port: int, use_tls: bool, client_id: string, username: string, password: string}
     */
    public function generateMqttConfig(Device $device): array
    {
        $token = Str::random(64);

        $device->setMetadata('mqtt_token', hash('sha256', $token));

        return [
            'broker' => config('mqtt.broker'),
            'port' => config('mqtt.port'),
            'use_tls' => config('mqtt.use_tls'),
            'client_id' => $device->device_id,
            'username' => $device->device_id,
            'password' => $token,
        ];
    }

    /**
     * Generate MQTT topic map for a device.
     *
     * @return array{data_in: string, data_out: string, commands: string, status: string}
     */
    public function generateTopics(Device $device): array
    {
        $thing = $device->thing;
        $thingUuid = $thing !== null ? $thing->uuid : 'unassigned';
        $deviceId = $device->device_id;

        return [
            'data_in' => "things/{$thingUuid}/devices/{$deviceId}/data/in",
            'data_out' => "things/{$thingUuid}/devices/{$deviceId}/data/out",
            'commands' => "things/{$thingUuid}/devices/{$deviceId}/commands",
            'status' => "things/{$thingUuid}/devices/{$deviceId}/status",
        ];
    }
}
