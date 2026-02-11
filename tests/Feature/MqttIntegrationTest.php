<?php declare(strict_types=1);

use App\Enums\CloudVariableType;
use App\Enums\DeviceStatus;
use App\Enums\VariableUpdatePolicy;
use App\Events\CloudVariableUpdated;
use App\Events\DeviceStatusChanged;
use App\Jobs\ProcessMqttMessage;
use App\Jobs\PublishMqttMessage;
use App\Models\CloudVariable;
use App\Models\Device;
use App\Models\Thing;
use App\Models\User;
use App\Services\MqttService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use ScienceStories\Mqtt\Client\InboundMessage;
use ScienceStories\Mqtt\Protocol\QoS;

// --- MqttService: Message Routing ---

it('dispatches ProcessMqttMessage for data/out topic', function (): void {
    Queue::fake();

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: 'smartiot/thing-uuid-123/data/out',
        payload: json_encode(['temperature' => 22.5]),
        qos: QoS::AtLeastOnce,
        retain: false,
        dup: false,
    );

    $service->handleMessage($message);

    Queue::assertPushed(ProcessMqttMessage::class, function (ProcessMqttMessage $job): bool {
        return $job->thingUuid === 'thing-uuid-123'
            && $job->payload === ['temperature' => 22.5];
    });
});

it('handles device online status synchronously', function (): void {
    Event::fake([DeviceStatusChanged::class]);

    $user = User::factory()->create();
    $result = Device::createWithCredentials([
        'name' => 'Test Device',
        'type' => 'esp32',
        'user_id' => $user->id,
    ]);
    $device = $result['device'];
    $device->status = DeviceStatus::Offline;
    $device->save();

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: "smartiot/{$device->device_id}/status",
        payload: json_encode(['status' => 'online', 'device_id' => $device->device_id]),
        qos: QoS::AtLeastOnce,
        retain: true,
        dup: false,
    );

    $service->handleMessage($message);

    expect($device->fresh()->status)->toBe(DeviceStatus::Online);

    Event::assertDispatched(DeviceStatusChanged::class, function (DeviceStatusChanged $event) use ($device): bool {
        return $event->device->id === $device->id
            && $event->oldStatus === DeviceStatus::Offline
            && $event->newStatus === DeviceStatus::Online;
    });
});

it('handles LWT offline message', function (): void {
    Event::fake([DeviceStatusChanged::class]);

    $user = User::factory()->create();
    $device = Device::factory()->online()->create(['user_id' => $user->id]);

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: "smartiot/{$device->device_id}/status",
        payload: 'offline',
        qos: QoS::AtLeastOnce,
        retain: true,
        dup: false,
    );

    $service->handleMessage($message);

    expect($device->fresh()->status)->toBe(DeviceStatus::Offline);

    Event::assertDispatched(DeviceStatusChanged::class, function (DeviceStatusChanged $event): bool {
        return $event->newStatus === DeviceStatus::Offline;
    });
});

it('does not fire event when status is unchanged', function (): void {
    Event::fake([DeviceStatusChanged::class]);

    $user = User::factory()->create();
    $device = Device::factory()->offline()->create(['user_id' => $user->id]);

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: "smartiot/{$device->device_id}/status",
        payload: 'offline',
        qos: QoS::AtLeastOnce,
        retain: true,
        dup: false,
    );

    $service->handleMessage($message);

    Event::assertNotDispatched(DeviceStatusChanged::class);
});

it('ignores unknown device on status message', function (): void {
    Event::fake([DeviceStatusChanged::class]);

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: 'smartiot/non-existent-device/status',
        payload: 'offline',
        qos: QoS::AtLeastOnce,
        retain: false,
        dup: false,
    );

    $service->handleMessage($message);

    Event::assertNotDispatched(DeviceStatusChanged::class);
});

it('ignores invalid JSON on data/out', function (): void {
    Queue::fake();

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: 'smartiot/thing-uuid/data/out',
        payload: 'not-json{{{',
        qos: QoS::AtLeastOnce,
        retain: false,
        dup: false,
    );

    $service->handleMessage($message);

    Queue::assertNotPushed(ProcessMqttMessage::class);
});

it('ignores unrecognized topic structure', function (): void {
    Queue::fake();
    Event::fake([DeviceStatusChanged::class, CloudVariableUpdated::class]);

    $service = app(MqttService::class);
    $message = new InboundMessage(
        topic: 'other/topic/entirely',
        payload: 'hello',
        qos: QoS::AtLeastOnce,
        retain: false,
        dup: false,
    );

    $service->handleMessage($message);

    Queue::assertNothingPushed();
    Event::assertNotDispatched(DeviceStatusChanged::class);
    Event::assertNotDispatched(CloudVariableUpdated::class);
});

it('returns correct subscription topics', function (): void {
    $service = app(MqttService::class);

    expect($service->getSubscriptionTopics())->toBe([
        'smartiot/+/data/out',
        'smartiot/+/cmd/up',
        'smartiot/+/status',
    ]);
});

// --- ProcessMqttMessage Job ---

it('updates cloud variable values', function (): void {
    Event::fake([CloudVariableUpdated::class]);

    $user = User::factory()->create();
    $device = Device::factory()->online()->create(['user_id' => $user->id]);
    $thing = Thing::factory()->create(['user_id' => $user->id, 'device_id' => $device->id]);

    $variable = CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
        'type' => CloudVariableType::Temperature,
        'update_policy' => VariableUpdatePolicy::OnChange,
    ]);

    $job = new ProcessMqttMessage($thing->uuid, ['temperature' => 22.5]);
    $job->handle();

    expect($variable->fresh()->last_value)->toBe(['value' => 22.5]);

    Event::assertDispatched(CloudVariableUpdated::class);
});

it('skips unknown variable names in payload', function (): void {
    Event::fake([CloudVariableUpdated::class]);

    $user = User::factory()->create();
    $device = Device::factory()->online()->create(['user_id' => $user->id]);
    $thing = Thing::factory()->create(['user_id' => $user->id, 'device_id' => $device->id]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
    ]);

    $job = new ProcessMqttMessage($thing->uuid, ['unknown_var' => 42]);
    $job->handle();

    Event::assertNotDispatched(CloudVariableUpdated::class);
});

it('handles non-existent thing gracefully', function (): void {
    Event::fake([CloudVariableUpdated::class]);

    $job = new ProcessMqttMessage('non-existent-uuid', ['temperature' => 22.5]);
    $job->handle();

    Event::assertNotDispatched(CloudVariableUpdated::class);
});

it('updates device last_activity_at on message processing', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->online()->create([
        'user_id' => $user->id,
        'last_activity_at' => null,
    ]);
    $thing = Thing::factory()->create(['user_id' => $user->id, 'device_id' => $device->id]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
    ]);

    $job = new ProcessMqttMessage($thing->uuid, ['temperature' => 22.5]);
    $job->handle();

    expect($device->fresh()->last_activity_at)->not->toBeNull();
});

it('processes multiple variables in a single message', function (): void {
    Event::fake([CloudVariableUpdated::class]);

    $user = User::factory()->create();
    $device = Device::factory()->online()->create(['user_id' => $user->id]);
    $thing = Thing::factory()->create(['user_id' => $user->id, 'device_id' => $device->id]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
        'update_policy' => VariableUpdatePolicy::OnChange,
    ]);

    CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'humidity',
        'update_policy' => VariableUpdatePolicy::OnChange,
    ]);

    $job = new ProcessMqttMessage($thing->uuid, [
        'temperature' => 22.5,
        'humidity' => 65,
    ]);
    $job->handle();

    Event::assertDispatched(CloudVariableUpdated::class, 2);
});

// --- CloudVariable::updateValue() ---

it('skips update for OnChange policy when value is unchanged', function (): void {
    Event::fake([CloudVariableUpdated::class]);

    $user = User::factory()->create();
    $thing = Thing::factory()->create(['user_id' => $user->id]);

    $variable = CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
        'update_policy' => VariableUpdatePolicy::OnChange,
        'last_value' => ['value' => 22.5],
    ]);

    $result = $variable->updateValue(22.5);

    expect($result)->toBeFalse();
    Event::assertNotDispatched(CloudVariableUpdated::class);
});

it('always updates for Periodically policy even when value unchanged', function (): void {
    Event::fake([CloudVariableUpdated::class]);

    $user = User::factory()->create();
    $thing = Thing::factory()->create(['user_id' => $user->id]);

    $variable = CloudVariable::factory()->create([
        'thing_id' => $thing->id,
        'variable_name' => 'temperature',
        'update_policy' => VariableUpdatePolicy::Periodically,
        'last_value' => ['value' => 22.5],
    ]);

    $result = $variable->updateValue(22.5);

    expect($result)->toBeTrue();
    Event::assertDispatched(CloudVariableUpdated::class);
});

// --- Outbound: MqttService ---

it('dispatches PublishMqttMessage for publishToThing', function (): void {
    Queue::fake();

    $service = app(MqttService::class);
    $service->publishToThing('thing-uuid-abc', ['led' => true]);

    Queue::assertPushed(PublishMqttMessage::class, function (PublishMqttMessage $job): bool {
        return $job->topic === 'smartiot/thing-uuid-abc/data/in'
            && json_decode($job->payload, true) === ['led' => true];
    });
});

it('dispatches PublishMqttMessage for publishCommand', function (): void {
    Queue::fake();

    $service = app(MqttService::class);
    $service->publishCommand('device-id-xyz', 'reboot');

    Queue::assertPushed(PublishMqttMessage::class, function (PublishMqttMessage $job): bool {
        return $job->topic === 'smartiot/device-id-xyz/cmd/down'
            && $job->payload === 'reboot';
    });
});
