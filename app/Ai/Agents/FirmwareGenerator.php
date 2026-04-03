<?php declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\CloudVariable;
use App\Models\Thing;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[MaxTokens(8192)]
#[Temperature(0.2)]
#[Timeout(120)]
final class FirmwareGenerator implements Agent
{
    use Promptable;

    public function __construct(
        public Thing $thing,
        public string $wifiSsid,
        public string $wifiPassword,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        $device = $this->thing->device;
        $deviceType = $device->type;
        $variables = $this->thing->cloudVariables()->get();

        $isRaspberryPi = $deviceType->value === 'raspberry_pi';
        $language = $isRaspberryPi ? 'Python' : 'C++ (Arduino framework)';

        $variableDeclarations = $variables->map(function (CloudVariable $v): string {
            return "- $v->declaration // {$v->permission->label()}, {$v->update_policy->label()}";
        })->implode("\n");

        $variableNames = $variables->pluck('variable_name')->implode(', ');

        $appUrl = config('app.url', 'https://smart-iot.test');

        return <<<INSTRUCTIONS
        You are an expert embedded systems firmware generator for the SmartIoT cloud platform.
        Generate COMPLETE, COMPILABLE $language code for a {$deviceType->label()} device.

        DEVICE INFORMATION:
        - Device ID: {$device->device_id}
        - Device Type: {$deviceType->label()}
        - Thing UUID: {$this->thing->uuid}
        - Thing Name: {$this->thing->name}

        CLOUD VARIABLES:
        {$variableDeclarations}

        Variable names used in MQTT JSON payloads: {$variableNames}

        MQTT TOPIC STRUCTURE:
        - Data Out (device → cloud): smartiot/{$this->thing->uuid}/data/out
        - Data In (cloud → device): smartiot/{$this->thing->uuid}/data/in
        - Command Up (device → cloud): smartiot/$device->device_id/cmd/up
        - Command Down (cloud → device): smartiot/$device->device_id/cmd/down
        - Status (device → cloud): smartiot/$device->device_id/status

        PROVISIONING API:
        POST $appUrl/api/v1/provision
        Request Body: {"device_id": "$device->device_id", "secret_key": "YOUR_SECRET_KEY"}
        Response contains: mqtt.host, mqtt.port, mqtt.use_tls, mqtt.client_id, mqtt.username, mqtt.password, topics object, variables array

        WIFI CREDENTIALS:
        - SSID: {$this->wifiSsid}
        - Password: {$this->wifiPassword}

        RECOMMENDED LIBRARIES: {$deviceType->libraries()}

        REQUIREMENTS:
        1. Generate a COMPLETE, COMPILABLE firmware file — no placeholders or TODOs
        2. Include all necessary #include directives (or imports for Python)
        3. Connect to WiFi with retry logic
        4. Call the provisioning API on first boot to get MQTT credentials and topics
        5. Store provisioning response (MQTT config) for reconnection
        6. Connect to the MQTT broker using credentials from provisioning
        7. Publish sensor data as JSON to the data_out topic: {"variable_name": value, ...}
        8. Subscribe to data_in topic for ReadWrite variables and parse incoming JSON
        9. Publish device status ("online") to the status topic on connect
        10. Set MQTT Last Will and Testament to publish "offline" to the status topic
        11. Send periodic heartbeat every 60 seconds
        12. Handle WiFi and MQTT reconnection gracefully
        13. Use a main loop that reads sensors and publishes data periodically
        14. Add clear comments explaining each section of the code
        15. For sensor reading, use placeholder functions with clear names (e.g., readTemperature()) that the user can customize for their specific hardware
        16. MQTT data format: JSON object with variable_name as keys and sensor values as values

        OUTPUT FORMAT:
        Output ONLY the raw source code. Do not wrap it in markdown code fences.
        Do not include any explanatory text before or after the code.
        INSTRUCTIONS;
    }
}
