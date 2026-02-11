<?php declare(strict_types=1);

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Livewire\Devices;
use App\Models\Device;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// --- Index ---

it('shows the devices index page', function (): void {
    Device::factory()->for($this->user)->create(['name' => 'My ESP32']);

    Livewire::test(Devices\Index::class)
        ->assertSee('My ESP32')
        ->assertSuccessful();
});

it('only shows devices belonging to the authenticated user', function (): void {
    $ownDevice = Device::factory()->for($this->user)->create(['name' => 'Own Device']);
    $otherDevice = Device::factory()->create(['name' => 'Other Device']);

    Livewire::test(Devices\Index::class)
        ->assertSee('Own Device')
        ->assertDontSee('Other Device');
});

it('can search devices by name', function (): void {
    Device::factory()->for($this->user)->create(['name' => 'Kitchen Sensor']);
    Device::factory()->for($this->user)->create(['name' => 'Garden Light']);

    Livewire::test(Devices\Index::class)
        ->set('search', 'Kitchen')
        ->assertSee('Kitchen Sensor')
        ->assertDontSee('Garden Light');
});

it('can filter devices by type', function (): void {
    Device::factory()->for($this->user)->esp32()->create(['name' => 'ESP Device']);
    Device::factory()->for($this->user)->arduino()->create(['name' => 'Arduino Device']);

    Livewire::test(Devices\Index::class)
        ->set('filterType', DeviceType::Esp32->value)
        ->assertSee('ESP Device')
        ->assertDontSee('Arduino Device');
});

it('can filter devices by status', function (): void {
    Device::factory()->for($this->user)->online()->create(['name' => 'Online Device']);
    Device::factory()->for($this->user)->offline()->create(['name' => 'Offline Device']);

    Livewire::test(Devices\Index::class)
        ->set('filterStatus', DeviceStatus::Online->value)
        ->assertSee('Online Device')
        ->assertDontSee('Offline Device');
});

it('can sort devices', function (): void {
    Device::factory()->for($this->user)->create(['name' => 'Alpha', 'created_at' => now()->subDay()]);
    Device::factory()->for($this->user)->create(['name' => 'Zeta', 'created_at' => now()]);

    Livewire::test(Devices\Index::class)
        ->call('sort', 'name')
        ->assertSeeInOrder(['Alpha', 'Zeta']);
});

it('can delete a device from the index', function (): void {
    $device = Device::factory()->for($this->user)->create(['name' => 'To Delete']);

    Livewire::test(Devices\Index::class)
        ->call('deleteDevice', $device->id);

    expect(Device::find($device->id))->toBeNull();
});

it('cannot delete another user\'s device', function (): void {
    $otherDevice = Device::factory()->create(['name' => 'Not Mine']);

    Livewire::test(Devices\Index::class)
        ->call('deleteDevice', $otherDevice->id);

    expect(Device::find($otherDevice->id))->not->toBeNull();
});

// --- Create Wizard ---

it('shows the create device wizard', function (): void {
    Livewire::test(Devices\Create::class)
        ->assertSee('Choose Device Type')
        ->assertSuccessful();
});

it('advances to step 2 when selecting a type', function (): void {
    Livewire::test(Devices\Create::class)
        ->call('selectType', DeviceType::Esp32->value)
        ->assertSet('step', 2)
        ->assertSet('type', DeviceType::Esp32->value)
        ->assertSee('Name Your Device');
});

it('can go back from step 2 to step 1', function (): void {
    Livewire::test(Devices\Create::class)
        ->call('selectType', DeviceType::Esp32->value)
        ->call('goBack')
        ->assertSet('step', 1);
});

it('creates a device and shows credentials', function (): void {
    Livewire::test(Devices\Create::class)
        ->call('selectType', DeviceType::Esp32->value)
        ->set('name', 'My New Device')
        ->call('createDevice')
        ->assertSet('step', 3)
        ->assertNotSet('deviceId', null)
        ->assertNotSet('secretKey', null)
        ->assertSee('Device Created Successfully');

    expect(Device::query()->where('name', 'My New Device')->first())
        ->not->toBeNull()
        ->user_id->toBe($this->user->id)
        ->type->toBe(DeviceType::Esp32);
});

it('validates device name is required', function (): void {
    Livewire::test(Devices\Create::class)
        ->call('selectType', DeviceType::Esp32->value)
        ->set('name', '')
        ->call('createDevice')
        ->assertHasErrors('name');
});

it('validates device name minimum length', function (): void {
    Livewire::test(Devices\Create::class)
        ->call('selectType', DeviceType::Esp32->value)
        ->set('name', 'A')
        ->call('createDevice')
        ->assertHasErrors('name');
});

// --- Show ---

it('shows device details', function (): void {
    $device = Device::factory()->for($this->user)->online()->create(['name' => 'Living Room']);

    Livewire::test(Devices\Show::class, ['device' => $device])
        ->assertSee('Living Room')
        ->assertSee($device->device_id)
        ->assertSee('Online')
        ->assertSuccessful();
});

it('returns 403 for another user\'s device', function (): void {
    $otherDevice = Device::factory()->create();

    Livewire::test(Devices\Show::class, ['device' => $otherDevice])
        ->assertForbidden();
});

it('can regenerate the secret key', function (): void {
    $device = Device::factory()->for($this->user)->create();

    Livewire::test(Devices\Show::class, ['device' => $device])
        ->call('regenerateSecretKey')
        ->assertDispatched('secret-key-regenerated');
});

it('can delete a device from the show page', function (): void {
    $device = Device::factory()->for($this->user)->create();

    Livewire::test(Devices\Show::class, ['device' => $device])
        ->call('deleteDevice')
        ->assertRedirectToRoute('devices.index');

    expect(Device::find($device->id))->toBeNull();
});

// --- Edit ---

it('shows the edit form with current name', function (): void {
    $device = Device::factory()->for($this->user)->create(['name' => 'Old Name']);

    Livewire::test(Devices\Edit::class, ['device' => $device])
        ->assertSet('name', 'Old Name')
        ->assertSuccessful();
});

it('can update the device name', function (): void {
    $device = Device::factory()->for($this->user)->create(['name' => 'Old Name']);

    Livewire::test(Devices\Edit::class, ['device' => $device])
        ->set('name', 'New Name')
        ->call('updateDevice')
        ->assertRedirect(route('devices.show', $device));

    expect($device->fresh()->name)->toBe('New Name');
});

it('validates name when editing', function (): void {
    $device = Device::factory()->for($this->user)->create();

    Livewire::test(Devices\Edit::class, ['device' => $device])
        ->set('name', '')
        ->call('updateDevice')
        ->assertHasErrors('name');
});

it('returns 403 when editing another user\'s device', function (): void {
    $otherDevice = Device::factory()->create();

    Livewire::test(Devices\Edit::class, ['device' => $otherDevice])
        ->assertForbidden();
});
