<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Command;
use ScienceStories\Mqtt\Client\Client;
use ScienceStories\Mqtt\Client\Options;
use ScienceStories\Mqtt\Protocol\MqttVersion;
use ScienceStories\Mqtt\Transport\TcpTransport;

final class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:listen {--timeout=0 : Seconds to run (0 = forever)}';

    protected $description = 'Subscribe to MQTT topics and process incoming device messages';

    private bool $shouldRun = true;

    public function handle(MqttService $mqttService): int
    {
        $timeout = (int) $this->option('timeout');

        $this->info('Connecting to MQTT broker...');

        $client = $this->createClient();
        $client->connect();

        $topics = $mqttService->getSubscriptionTopics();
        $client->subscribe($topics, qos: 1);

        $this->info('Subscribed to: '.implode(', ', $topics));
        $this->info('Listening for messages'.($timeout > 0 ? " ({$timeout}s timeout)" : '').'...');

        $this->registerSignalHandlers();

        $deadline = $timeout > 0 ? microtime(true) + $timeout : null;

        foreach ($client->messages(0.2) as $message) {
            $mqttService->handleMessage($message);

            if (! $this->shouldRun) {
                $this->info('Shutting down gracefully...');
                break;
            }

            if ($deadline !== null && microtime(true) >= $deadline) {
                $this->info('Timeout reached, stopping.');
                break;
            }
        }

        $client->disconnect();
        $this->info('Disconnected from MQTT broker.');

        return self::SUCCESS;
    }

    private function createClient(): Client
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $scheme = (string) config('mqtt.scheme', 'tls');
        $useTls = $scheme === 'tls';

        $options = new Options(
            host: $host,
            port: $port,
            version: MqttVersion::V5_0,
        );

        $options = $options
            ->withClientId('smartiot_cloud_listener')
            ->withKeepAlive(60)
            ->withCleanSession(false);

        $username = config('mqtt.username');
        if ($username !== null) {
            $options = $options->withUser((string) $username, (string) config('mqtt.password'));
        }

        if ($useTls) {
            $options = $options->withTls([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
        }

        return new Client($options, new TcpTransport());
    }

    private function registerSignalHandlers(): void
    {
        if (! function_exists('pcntl_signal')) {
            return;
        }

        $handler = function (): void {
            $this->shouldRun = false;
        };

        pcntl_signal(SIGINT, $handler);
        pcntl_signal(SIGTERM, $handler);
    }
}
