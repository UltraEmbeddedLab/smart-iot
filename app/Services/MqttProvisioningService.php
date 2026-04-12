<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Device;
use JsonException;
use Random\RandomException;
use ScienceStories\Mqtt\Client\Client;
use ScienceStories\Mqtt\Client\Options;
use ScienceStories\Mqtt\Client\WillOptions;
use ScienceStories\Mqtt\Easy\Mqtt;
use ScienceStories\Mqtt\Protocol\MqttVersion;
use ScienceStories\Mqtt\Protocol\QoS;
use ScienceStories\Mqtt\Transport\TcpTransport;

final class MqttProvisioningService
{
    /**
     * Generate MQTT connection config for a device.
     *
     * All devices share the broker credentials configured in .env. Isolation
     * between devices is enforced through the topic layout in generateTopics(),
     * not through per-device broker authentication.
     *
     * @return array{host: string, port: int, use_tls: bool, client_id: string, username: string, password: string}
     */
    public function generateMqttConfig(Device $device): array
    {
        return [
            'host' => config('mqtt.host'),
            'port' => config('mqtt.port'),
            'use_tls' => config('mqtt.scheme') === 'tls',
            'client_id' => "smartiot_$device->device_id",
            'username' => (string) (config('mqtt.username') ?? $device->device_id),
            'password' => (string) config('mqtt.password'),
        ];
    }

    /**
     * Generate MQTT topic map for a device following the smartiot topic convention.
     *
     * @return array{data_out: string, data_in: string, cmd_up: string, cmd_down: string, status: string}
     */
    public function generateTopics(Device $device): array
    {
        $thing = $device->thing;
        $thingId = $thing !== null ? $thing->uuid : 'unassigned';
        $deviceId = $device->device_id;

        return [
            'data_out' => "smartiot/$thingId/data/out",
            'data_in' => "smartiot/$thingId/data/in",
            'cmd_up' => "smartiot/$deviceId/cmd/up",
            'cmd_down' => "smartiot/$deviceId/cmd/down",
            'status' => "smartiot/$deviceId/status",
        ];
    }

    /**
     * Build MQTT client options for a device.
     */
    public function buildClientOptions(Device $device): Options
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $scheme = (string) config('mqtt.scheme', 'tls');
        $statusTopic = "smartiot/$device->device_id/status";

        $options = new Options(
            host: $host,
            port: $port,
            version: MqttVersion::V5_0,
        )
            ->withClientId("smartiot_$device->device_id")
            ->withKeepAlive(60)
            ->withCleanSession(false)
            ->withWill(new WillOptions(
                topic: $statusTopic,
                payload: 'offline',
                qos: QoS::AtLeastOnce,
                retain: true,
            ));

        $username = config('mqtt.username');
        if ($username !== null) {
            $options = $options->withUser((string) $username, (string) config('mqtt.password'));
        }

        if ($scheme === 'tls') {
            $options = $options->withTls([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
        }

        return $options;
    }

    /**
     * Publish the device online status to MQTT broker.
     *
     * @throws JsonException
     * @throws RandomException
     */
    public function publishDeviceOnline(Device $device): void
    {
        $statusTopic = "smartiot/$device->device_id/status";
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $scheme = (string) config('mqtt.scheme', 'tls');
        $useTls = $scheme === 'tls';

        Mqtt::publish(
            host: $host,
            topic: $statusTopic,
            payload: json_encode([
                'status' => 'online',
                'device_id' => $device->device_id,
                'timestamp' => now()->toIso8601String(),
            ], JSON_THROW_ON_ERROR),
            port: $port,
            tls: $useTls,
            username: config('mqtt.username'),
            password: config('mqtt.password'),
            qos: QoS::AtLeastOnce,
            retain: true,
            tlsOptions: $useTls ? [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ] : null,
        );
    }

    /**
     * Create a connected MQTT client for a device.
     */
    public function createClient(Device $device): Client
    {
        $options = $this->buildClientOptions($device);
        $transport = new TcpTransport();

        $client = new Client($options, $transport);
        $client->connect();

        return $client;
    }
}
