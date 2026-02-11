<?php declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use ScienceStories\Mqtt\Easy\Mqtt;
use ScienceStories\Mqtt\Protocol\QoS;

final class PublishMqttMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public string $topic,
        public string $payload,
        public bool $retain = false,
    ) {}

    public function handle(): void
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $scheme = (string) config('mqtt.scheme', 'tls');
        $useTls = $scheme === 'tls';

        Mqtt::publish(
            host: $host,
            topic: $this->topic,
            payload: $this->payload,
            port: $port,
            tls: $useTls,
            username: config('mqtt.username'),
            password: config('mqtt.password'),
            qos: QoS::AtLeastOnce,
            retain: $this->retain,
            tlsOptions: $useTls ? [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ] : null,
        );
    }
}
