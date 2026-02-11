<?php declare(strict_types=1);

use App\Enums\CloudVariableType;
use App\Enums\DeviceStatus;
use App\Enums\VariablePermission;
use App\Enums\VariableUpdatePolicy;
use App\Models\CloudVariable;
use App\Models\Device;
use App\Models\Thing;
use App\Models\User;

// --- Provisioning ---

it('provisions a device with valid credentials', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test ESP32',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/provision', [
        'device_id' => $result['device']->device_id,
        'secret_key' => $result['secret_key'],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'status',
                'device_id',
                'thing_id',
                'mqtt' => ['broker', 'port', 'use_tls', 'client_id', 'username', 'password'],
                'topics' => ['data_in', 'data_out', 'commands', 'status'],
                'variables',
            ],
        ])
        ->assertJsonPath('data.status', 'provisioned')
        ->assertJsonPath('data.device_id', $result['device']->device_id);

    expect($result['device']->fresh()->status)->toBe(DeviceStatus::Online);
});

it('returns 422 for missing device_id', function (): void {
    $this->postJson('/api/v1/provision', [
        'secret_key' => 'some-secret-key',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['device_id']);
});

it('returns 422 for missing secret_key', function (): void {
    $this->postJson('/api/v1/provision', [
        'device_id' => fake()->uuid(),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['secret_key']);
});

it('returns 401 for non-existent device', function (): void {
    $this->postJson('/api/v1/provision', [
        'device_id' => fake()->uuid(),
        'secret_key' => 'some-secret-key-value',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid device credentials.');
});

it('returns 401 for wrong secret_key', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $this->postJson('/api/v1/provision', [
        'device_id' => $result['device']->device_id,
        'secret_key' => 'wrong-secret-key-value',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid device credentials.');
});

it('returns correct MQTT topics with thing and device identifiers', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);
    $device = $result['device'];

    $thing = Thing::factory()->create([
        'user_id' => $user->id,
        'device_id' => $device->id,
    ]);

    $response = $this->postJson('/api/v1/provision', [
        'device_id' => $device->device_id,
        'secret_key' => $result['secret_key'],
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.thing_id', $thing->uuid)
        ->assertJsonPath('data.topics.data_in', "things/{$thing->uuid}/devices/{$device->device_id}/data/in")
        ->assertJsonPath('data.topics.data_out', "things/{$thing->uuid}/devices/{$device->device_id}/data/out")
        ->assertJsonPath('data.topics.commands', "things/{$thing->uuid}/devices/{$device->device_id}/commands")
        ->assertJsonPath('data.topics.status', "things/{$thing->uuid}/devices/{$device->device_id}/status");
});

it('returns cloud variables in provisioning response', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);
    $device = $result['device'];

    $thing = Thing::factory()->create([
        'user_id' => $user->id,
        'device_id' => $device->id,
    ]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
        'type' => CloudVariableType::Temperature,
        'permission' => VariablePermission::ReadOnly,
        'update_policy' => VariableUpdatePolicy::Periodically,
        'update_parameter' => 30,
    ]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'led_switch',
        'type' => CloudVariableType::Switch,
        'permission' => VariablePermission::ReadWrite,
        'update_policy' => VariableUpdatePolicy::OnChange,
    ]);

    $response = $this->postJson('/api/v1/provision', [
        'device_id' => $device->device_id,
        'secret_key' => $result['secret_key'],
    ]);

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data.variables')
        ->assertJsonFragment([
            'variable_name' => 'temperature',
            'type' => 'temperature',
            'permission' => 'read_only',
            'update_policy' => 'periodically',
            'update_parameter' => '30.00',
        ])
        ->assertJsonFragment([
            'variable_name' => 'led_switch',
            'type' => 'switch',
            'permission' => 'read_write',
            'update_policy' => 'on_change',
        ]);
});

it('handles device with no associated thing', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Orphan Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/provision', [
        'device_id' => $result['device']->device_id,
        'secret_key' => $result['secret_key'],
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.thing_id', null)
        ->assertJsonPath('data.variables', []);
});

it('stores hashed MQTT token in device metadata', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/provision', [
        'device_id' => $result['device']->device_id,
        'secret_key' => $result['secret_key'],
    ]);

    $response->assertSuccessful();

    $device = $result['device']->fresh();
    $mqttPassword = $response->json('data.mqtt.password');

    expect($device->getMetadata('mqtt_token'))->toBe(hash('sha256', $mqttPassword));
});

// --- Heartbeat ---

it('updates last_activity_at via heartbeat', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/v1/heartbeat', [], [
        'X-Device-ID' => $result['device']->device_id,
        'X-Secret-Key' => $result['secret_key'],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['status', 'last_activity_at'])
        ->assertJsonPath('status', 'ok');

    expect($result['device']->fresh()->last_activity_at)->not->toBeNull();
});

it('returns 401 for heartbeat without credentials', function (): void {
    $this->postJson('/api/v1/heartbeat')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Device authentication required.');
});

it('returns 401 for heartbeat with invalid credentials', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $this->postJson('/api/v1/heartbeat', [], [
        'X-Device-ID' => $result['device']->device_id,
        'X-Secret-Key' => 'wrong-key',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid device credentials.');
});

// --- Config ---

it('returns config for authenticated device', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);
    $device = $result['device'];

    $thing = Thing::factory()->create([
        'user_id' => $user->id,
        'device_id' => $device->id,
    ]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'humidity',
        'type' => CloudVariableType::Humidity,
    ]);

    $response = $this->getJson("/api/v1/config/{$device->device_id}", [
        'X-Device-ID' => $device->device_id,
        'X-Secret-Key' => $result['secret_key'],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => ['device_id', 'status', 'thing_id', 'variables'],
        ])
        ->assertJsonPath('data.device_id', $device->device_id)
        ->assertJsonPath('data.thing_id', $thing->uuid)
        ->assertJsonCount(1, 'data.variables')
        ->assertJsonPath('data.variables.0.variable_name', 'humidity');
});

it('returns 401 for config without authentication', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $this->getJson("/api/v1/config/{$result['device']->device_id}")
        ->assertUnauthorized();
});

it('returns 403 when requesting another devices config', function (): void {
    $user = User::factory()->create();

    $result1 = Device::createWithCredentials([
        'name' => 'Device A',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $result2 = Device::createWithCredentials([
        'name' => 'Device B',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    $this->getJson("/api/v1/config/{$result2['device']->device_id}", [
        'X-Device-ID' => $result1['device']->device_id,
        'X-Secret-Key' => $result1['secret_key'],
    ])->assertForbidden()
        ->assertJsonPath('message', 'You may only access your own device configuration.');
});

// --- Rate Limiting ---

it('rate limits the provision endpoint', function (): void {
    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/provision', [
            'device_id' => $result['device']->device_id,
            'secret_key' => $result['secret_key'],
        ]);
    }

    $this->postJson('/api/v1/provision', [
        'device_id' => $result['device']->device_id,
        'secret_key' => $result['secret_key'],
    ])->assertTooManyRequests();
});
