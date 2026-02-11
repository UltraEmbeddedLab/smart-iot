<?php declare(strict_types=1);

use App\Models\Device;
use App\Models\Thing;
use App\Models\ThingTag;
use App\Models\User;
use Illuminate\Support\Str;

it('auto-generates a UUID when creating a thing', function (): void {
    $thing = Thing::factory()->create();

    expect($thing->uuid)->toBeString();
    expect(Str::isUuid($thing->uuid))->toBeTrue();
});

it('defaults timezone to UTC', function (): void {
    $thing = Thing::factory()->create();

    expect($thing->timezone)->toBe('UTC');
});

it('belongs to a user', function (): void {
    $user = User::factory()->create();
    $thing = Thing::factory()->for($user)->create();

    expect($thing->user->id)->toBe($user->id);
});

it('can belong to a device', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create();
    $thing = Thing::factory()->for($user)->create(['device_id' => $device->id]);

    expect($thing->device->id)->toBe($device->id);
});

it('can exist without a device', function (): void {
    $thing = Thing::factory()->create(['device_id' => null]);

    expect($thing->device)->toBeNull();
});

it('has many tags', function (): void {
    $thing = Thing::factory()->create();
    ThingTag::factory()->for($thing)->create(['key' => 'env', 'value' => 'production']);
    ThingTag::factory()->for($thing)->create(['key' => 'location', 'value' => 'office']);

    expect($thing->tags)->toHaveCount(2);
});

it('cascades delete to tags', function (): void {
    $thing = Thing::factory()->create();
    ThingTag::factory()->for($thing)->count(3)->create();

    expect(ThingTag::query()->where('thing_id', $thing->id)->count())->toBe(3);

    $thing->delete();

    expect(ThingTag::query()->where('thing_id', $thing->id)->count())->toBe(0);
});

it('nullifies thing device_id when device is deleted', function (): void {
    $user = User::factory()->create();
    $device = Device::factory()->for($user)->create();
    $thing = Thing::factory()->for($user)->create(['device_id' => $device->id]);

    $device->delete();

    expect($thing->fresh()->device_id)->toBeNull();
});

it('generates unique UUIDs for each thing', function (): void {
    $thing1 = Thing::factory()->create();
    $thing2 = Thing::factory()->create();

    expect($thing1->uuid)->not->toBe($thing2->uuid);
});
