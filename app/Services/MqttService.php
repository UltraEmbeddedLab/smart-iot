<?php declare(strict_types=1);

namespace App\Services;

use App\Enums\DeviceStatus;
use App\Events\DeviceStatusChanged;
use App\Jobs\ProcessMqttMessage;
use App\Jobs\PublishMqttMessage;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use ScienceStories\Mqtt\Client\InboundMessage;

final class MqttService
{
    /**
     * Get the wildcard topics the cloud listener should subscribe to.
     *
     * @return list<string>
     */
    public function getSubscriptionTopics(): array
    {
        return [
            'smartiot/+/data/out',
            'smartiot/+/cmd/up',
            'smartiot/+/status',
        ];
    }

    /**
     * Route an inbound MQTT message to the appropriate handler.
     */
    public function handleMessage(InboundMessage $message): void
    {
        $segments = explode('/', $message->topic);

        if (count($segments) < 3 || $segments[0] !== 'smartiot') {
            Log::debug('MqttService: Ignoring unrecognized topic', ['topic' => $message->topic]);

            return;
        }

        $identifier = $segments[1];
        $channel = implode('/', array_slice($segments, 2));

        match ($channel) {
            'data/out' => $this->handleDataOut($identifier, $message->payload),
            'status' => $this->handleStatus($identifier, $message->payload),
            'cmd/up' => $this->handleCommandUp($identifier, $message->payload),
            default => Log::debug('MqttService: Unknown channel', ['topic' => $message->topic]),
        };
    }

    /**
     * Publish values to a thing's data/in topic.
     *
     * @param  array<string, mixed>  $values
     */
    public function publishToThing(string $thingUuid, array $values): void
    {
        PublishMqttMessage::dispatch(
            topic: "smartiot/{$thingUuid}/data/in",
            payload: json_encode($values, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Publish a command to a device's cmd/down topic.
     */
    public function publishCommand(string $deviceId, string $command): void
    {
        PublishMqttMessage::dispatch(
            topic: "smartiot/{$deviceId}/cmd/down",
            payload: $command,
        );
    }

    /**
     * Handle data/out messages: decode JSON and dispatch queued processing.
     */
    private function handleDataOut(string $thingUuid, string $payload): void
    {
        $data = json_decode($payload, true);

        if (! is_array($data)) {
            Log::warning('MqttService: Invalid JSON on data/out', [
                'thing_uuid' => $thingUuid,
                'payload' => $payload,
            ]);

            return;
        }

        ProcessMqttMessage::dispatch($thingUuid, $data);
    }

    /**
     * Handle status messages synchronously for instant presence updates.
     */
    private function handleStatus(string $deviceId, string $payload): void
    {
        $device = Device::query()->where('device_id', $deviceId)->first();

        if ($device === null) {
            Log::debug('MqttService: Unknown device for status', ['device_id' => $deviceId]);

            return;
        }

        $oldStatus = $device->status;

        if ($payload === 'offline') {
            if ($oldStatus === DeviceStatus::Offline) {
                return;
            }

            $device->markAsOffline();
            DeviceStatusChanged::dispatch($device, $oldStatus, DeviceStatus::Offline);

            return;
        }

        $statusData = json_decode($payload, true);

        if (is_array($statusData) && ($statusData['status'] ?? null) === 'online') {
            if ($oldStatus === DeviceStatus::Online) {
                return;
            }

            $device->markAsOnline();
            DeviceStatusChanged::dispatch($device, $oldStatus, DeviceStatus::Online);

            return;
        }

        Log::debug('MqttService: Unrecognized status payload', [
            'device_id' => $deviceId,
            'payload' => $payload,
        ]);
    }

    /**
     * Handle cmd/up messages (stub for future implementation).
     */
    private function handleCommandUp(string $deviceId, string $payload): void
    {
        Log::debug('MqttService: cmd/up received (not yet implemented)', [
            'device_id' => $deviceId,
            'payload' => $payload,
        ]);
    }
}
