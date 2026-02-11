<?php declare(strict_types=1);

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Str;

it('can create a device with auto-generated device_id', function (): void {
    $device = Device::factory()->create([
        'name' => 'My ESP32',
        'type' => DeviceType::Esp32,
    ]);

    expect($device->device_id)->toBeString();
    expect(Str::isUuid($device->device_id))->toBeTrue();
    expect($device->name)->toBe('My ESP32');
    expect($device->type)->toBe(DeviceType::Esp32);
});

it('sets default status to provisioning when creating a device', function (): void {
    $device = Device::factory()->create();

    expect($device->status)->toBe(DeviceStatus::Provisioning);
});

it('can generate and verify a secret key', function (): void {
    $device = Device::factory()->create();

    $plainSecretKey = $device->generateSecretKey();

    expect($plainSecretKey)->toBeString();
    expect(mb_strlen($plainSecretKey))->toBe(32);
    expect($device->verifySecretKey($plainSecretKey))->toBeTrue();
    expect($device->verifySecretKey('wrong-key'))->toBeFalse();
});

it('hides secret key in serialization', function (): void {
    $device = Device::factory()->create();

    $array = $device->toArray();

    expect($array)->not->toHaveKey('secret_key');
});

it('can mark device as online', function (): void {
    $device = Device::factory()->create();

    $device->markAsOnline();

    expect($device->status)->toBe(DeviceStatus::Online);
    expect($device->last_activity_at)->not->toBeNull();
});

it('can mark device as offline', function (): void {
    $device = Device::factory()->online()->create();

    $device->markAsOffline();

    expect($device->status)->toBe(DeviceStatus::Offline);
});

it('can check device status', function (): void {
    $onlineDevice = Device::factory()->online()->create();
    $offlineDevice = Device::factory()->offline()->create();
    $provisioningDevice = Device::factory()->provisioning()->create();

    expect($onlineDevice->isOnline())->toBeTrue();
    expect($onlineDevice->isOffline())->toBeFalse();
    expect($onlineDevice->isProvisioning())->toBeFalse();

    expect($offlineDevice->isOffline())->toBeTrue();
    expect($offlineDevice->isOnline())->toBeFalse();

    expect($provisioningDevice->isProvisioning())->toBeTrue();
    expect($provisioningDevice->isOnline())->toBeFalse();
});

it('can store and retrieve metadata', function (): void {
    $device = Device::factory()->create();

    $device->setMetadata('firmware_version', '1.0.0');
    $device->setMetadata('ip_address', '192.168.1.100');

    expect($device->getMetadata('firmware_version'))->toBe('1.0.0');
    expect($device->getMetadata('ip_address'))->toBe('192.168.1.100');
    expect($device->getMetadata('non_existent', 'default'))->toBe('default');
});

it('casts type to DeviceType enum', function (): void {
    $device = Device::factory()->arduino()->create();

    expect($device->type)->toBeInstanceOf(DeviceType::class);
    expect($device->type)->toBe(DeviceType::Arduino);
    expect($device->type->label())->toBe('Arduino');
});

it('casts status to DeviceStatus enum', function (): void {
    $device = Device::factory()->online()->create();

    expect($device->status)->toBeInstanceOf(DeviceStatus::class);
    expect($device->status)->toBe(DeviceStatus::Online);
    expect($device->status->label())->toBe('Online');
    expect($device->status->color())->toBe('green');
});

it('casts last_activity_at to datetime', function (): void {
    $device = Device::factory()->online()->create();

    expect($device->last_activity_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('can create device with typical metadata', function (): void {
    $device = Device::factory()->withTypicalMetadata()->create();

    expect($device->metadata)->toBeArray();
    expect($device->metadata)->toHaveKeys(['firmware_version', 'ip_address', 'mac_address', 'last_boot']);
});

it('generates unique device_id for each device', function (): void {
    $device1 = Device::factory()->create();
    $device2 = Device::factory()->create();

    expect($device1->device_id)->not->toBe($device2->device_id);
});

it('can create device with credentials and verify secret key', function (): void {
    $user = User::factory()->create();

    $result = Device::createWithCredentials([
        'name' => 'IoT Sensor',
        'type' => DeviceType::Esp32,
        'user_id' => $user->id,
    ]);

    expect($result)->toHaveKeys(['device', 'secret_key']);
    expect($result['device'])->toBeInstanceOf(Device::class);
    expect($result['secret_key'])->toBeString();
    expect(mb_strlen($result['secret_key']))->toBe(32);

    $device = $result['device'];
    expect($device->name)->toBe('IoT Sensor');
    expect($device->type)->toBe(DeviceType::Esp32);
    expect($device->verifySecretKey($result['secret_key']))->toBeTrue();
    expect($device->verifySecretKey('invalid-key'))->toBeFalse();
});
