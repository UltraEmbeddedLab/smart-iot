<?php declare(strict_types=1);

use App\Ai\Agents\FirmwareGenerator;
use App\Livewire\Things\GenerateFirmware;
use App\Models\CloudVariable;
use App\Models\Device;
use App\Models\Firmware;
use App\Models\Thing;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->device = Device::factory()->for($this->user)->create();
    $this->thing = Thing::factory()->for($this->user)->create(['device_id' => $this->device->id]);
    CloudVariable::factory()->for($this->thing)->create([
        'name' => 'Temperature',
        'variable_name' => 'temperature',
    ]);
});

it('renders the generate firmware page', function (): void {
    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->assertSee('Generate Firmware')
        ->assertSee($this->thing->name)
        ->assertSee($this->device->name)
        ->assertSuccessful();
});

it('returns 403 for another user\'s thing', function (): void {
    $otherThing = Thing::factory()->create();
    $otherDevice = Device::factory()->for($otherThing->user)->create();
    $otherThing->update(['device_id' => $otherDevice->id]);

    Livewire::test(GenerateFirmware::class, ['thing' => $otherThing])
        ->assertForbidden();
});

it('aborts when thing has no device', function (): void {
    $thingWithoutDevice = Thing::factory()->for($this->user)->create(['device_id' => null]);

    Livewire::test(GenerateFirmware::class, ['thing' => $thingWithoutDevice])
        ->assertNotFound();
});

it('renders page when thing has no cloud variables', function (): void {
    $newDevice = Device::factory()->for($this->user)->create();
    $thingWithoutVars = Thing::factory()->for($this->user)->create([
        'device_id' => $newDevice->id,
    ]);

    Livewire::test(GenerateFirmware::class, ['thing' => $thingWithoutVars])
        ->assertSee('No cloud variables defined')
        ->assertSuccessful();
});

it('validates wifi ssid is required', function (): void {
    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->set('wifiSsid', '')
        ->set('wifiPassword', 'password123')
        ->call('generateCode')
        ->assertHasErrors('wifiSsid');
});

it('validates wifi password is required', function (): void {
    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->set('wifiSsid', 'MyNetwork')
        ->set('wifiPassword', '')
        ->call('generateCode')
        ->assertHasErrors('wifiPassword');
});

it('generates firmware code using the AI agent', function (): void {
    FirmwareGenerator::fake([
        '// Generated firmware code for ESP32',
    ]);

    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->set('wifiSsid', 'MyNetwork')
        ->set('wifiPassword', 'password123')
        ->call('generateCode')
        ->assertSet('errorMessage', '')
        ->assertSee('Generated firmware code for ESP32');

    FirmwareGenerator::assertPrompted('Generate the complete firmware code for this device.');
});

it('handles AI generation failure gracefully', function (): void {
    FirmwareGenerator::fake(function () {
        throw new RuntimeException('AI service unavailable');
    });

    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->set('wifiSsid', 'MyNetwork')
        ->set('wifiPassword', 'password123')
        ->call('generateCode')
        ->assertSet('generatedCode', '')
        ->assertSee('Failed to generate firmware');
});

it('can save generated firmware', function (): void {
    FirmwareGenerator::fake([
        '// ESP32 firmware code',
    ]);

    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->set('wifiSsid', 'MyNetwork')
        ->set('wifiPassword', 'password123')
        ->call('generateCode')
        ->set('firmwareName', 'v1.0 Temperature')
        ->call('saveFirmware');

    $firmware = Firmware::query()->where('thing_id', $this->thing->id)->first();

    expect($firmware)
        ->not->toBeNull()
        ->name->toBe('v1.0 Temperature')
        ->code->toBe('// ESP32 firmware code')
        ->device_type->value->toBe($this->device->type->value);
});

it('validates firmware name when saving', function (): void {
    FirmwareGenerator::fake(['// code']);

    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->set('wifiSsid', 'MyNetwork')
        ->set('wifiPassword', 'password123')
        ->call('generateCode')
        ->set('firmwareName', '')
        ->call('saveFirmware')
        ->assertHasErrors('firmwareName');
});

it('can delete saved firmware', function (): void {
    $firmware = Firmware::factory()->create(['thing_id' => $this->thing->id]);

    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->call('deleteFirmware', $firmware->id);

    expect(Firmware::find($firmware->id))->toBeNull();
});

it('can load saved firmware into code view', function (): void {
    $firmware = Firmware::factory()->create([
        'thing_id' => $this->thing->id,
        'code' => '// Saved firmware code',
    ]);

    Livewire::test(GenerateFirmware::class, ['thing' => $this->thing])
        ->call('loadFirmware', $firmware->id)
        ->assertSet('generatedCode', '// Saved firmware code');
});
