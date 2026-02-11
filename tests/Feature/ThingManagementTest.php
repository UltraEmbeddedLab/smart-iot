<?php declare(strict_types=1);

use App\Livewire\Things;
use App\Models\Device;
use App\Models\Thing;
use App\Models\ThingTag;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// --- Index ---

it('shows the things index page', function (): void {
    Thing::factory()->for($this->user)->create(['name' => 'My Thing']);

    Livewire::test(Things\Index::class)
        ->assertSee('My Thing')
        ->assertSuccessful();
});

it('only shows things belonging to the authenticated user', function (): void {
    Thing::factory()->for($this->user)->create(['name' => 'Own Thing']);
    Thing::factory()->create(['name' => 'Other Thing']);

    Livewire::test(Things\Index::class)
        ->assertSee('Own Thing')
        ->assertDontSee('Other Thing');
});

it('can search things by name', function (): void {
    Thing::factory()->for($this->user)->create(['name' => 'Kitchen Climate']);
    Thing::factory()->for($this->user)->create(['name' => 'Garden Irrigation']);

    Livewire::test(Things\Index::class)
        ->set('search', 'Kitchen')
        ->assertSee('Kitchen Climate')
        ->assertDontSee('Garden Irrigation');
});

it('can sort things', function (): void {
    Thing::factory()->for($this->user)->create(['name' => 'Alpha', 'created_at' => now()->subDay()]);
    Thing::factory()->for($this->user)->create(['name' => 'Zeta', 'created_at' => now()]);

    Livewire::test(Things\Index::class)
        ->call('sort', 'name')
        ->assertSeeInOrder(['Alpha', 'Zeta']);
});

it('can delete a thing from the index', function (): void {
    $thing = Thing::factory()->for($this->user)->create(['name' => 'To Delete']);

    Livewire::test(Things\Index::class)
        ->call('deleteThing', $thing->id);

    expect(Thing::find($thing->id))->toBeNull();
});

it('cannot delete another user\'s thing from index', function (): void {
    $otherThing = Thing::factory()->create(['name' => 'Not Mine']);

    Livewire::test(Things\Index::class)
        ->call('deleteThing', $otherThing->id);

    expect(Thing::find($otherThing->id))->not->toBeNull();
});

it('shows device name on index when thing has a device', function (): void {
    $device = Device::factory()->for($this->user)->create(['name' => 'ESP32 Board']);
    Thing::factory()->for($this->user)->create(['name' => 'My Thing', 'device_id' => $device->id]);

    Livewire::test(Things\Index::class)
        ->assertSee('ESP32 Board');
});

// --- Create ---

it('shows the create thing form', function (): void {
    Livewire::test(Things\Create::class)
        ->assertSee('Create Thing')
        ->assertSuccessful();
});

it('can create a thing', function (): void {
    Livewire::test(Things\Create::class)
        ->set('name', 'New Thing')
        ->set('timezone', 'Europe/Berlin')
        ->call('createThing')
        ->assertRedirectToRoute('things.index');

    expect(Thing::query()->where('name', 'New Thing')->first())
        ->not->toBeNull()
        ->user_id->toBe($this->user->id)
        ->timezone->toBe('Europe/Berlin');
});

it('can create a thing with a device', function (): void {
    $device = Device::factory()->for($this->user)->create();

    Livewire::test(Things\Create::class)
        ->set('name', 'Device Thing')
        ->set('timezone', 'UTC')
        ->set('device_id', $device->id)
        ->call('createThing')
        ->assertRedirectToRoute('things.index');

    expect(Thing::query()->where('name', 'Device Thing')->first())
        ->not->toBeNull()
        ->device_id->toBe($device->id);
});

it('validates name is required when creating', function (): void {
    Livewire::test(Things\Create::class)
        ->set('name', '')
        ->call('createThing')
        ->assertHasErrors('name');
});

it('validates name minimum length when creating', function (): void {
    Livewire::test(Things\Create::class)
        ->set('name', 'A')
        ->call('createThing')
        ->assertHasErrors('name');
});

it('validates timezone when creating', function (): void {
    Livewire::test(Things\Create::class)
        ->set('name', 'My Thing')
        ->set('timezone', 'Invalid/Zone')
        ->call('createThing')
        ->assertHasErrors('timezone');
});

it('only shows available devices in create form', function (): void {
    $freeDevice = Device::factory()->for($this->user)->create(['name' => 'Free Device']);
    $takenDevice = Device::factory()->for($this->user)->create(['name' => 'Taken Device']);
    Thing::factory()->for($this->user)->create(['device_id' => $takenDevice->id]);

    Livewire::test(Things\Create::class)
        ->assertSee('Free Device')
        ->assertDontSee('Taken Device');
});

it('prevents assigning another user\'s device', function (): void {
    $otherDevice = Device::factory()->create();

    Livewire::test(Things\Create::class)
        ->set('name', 'Sneaky Thing')
        ->set('timezone', 'UTC')
        ->set('device_id', $otherDevice->id)
        ->call('createThing')
        ->assertHasErrors('device_id');
});

// --- Show ---

it('shows thing details', function (): void {
    $thing = Thing::factory()->for($this->user)->create(['name' => 'Living Room']);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->assertSee('Living Room')
        ->assertSee($thing->uuid)
        ->assertSuccessful();
});

it('returns 403 for another user\'s thing', function (): void {
    $otherThing = Thing::factory()->create();

    Livewire::test(Things\Show::class, ['thing' => $otherThing])
        ->assertForbidden();
});

it('can delete a thing from the show page', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->call('deleteThing')
        ->assertRedirectToRoute('things.index');

    expect(Thing::find($thing->id))->toBeNull();
});

it('shows device info on show page when thing has a device', function (): void {
    $device = Device::factory()->for($this->user)->create(['name' => 'My ESP32']);
    $thing = Thing::factory()->for($this->user)->create(['device_id' => $device->id]);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->assertSee('My ESP32');
});

it('can detach a device from a thing', function (): void {
    $device = Device::factory()->for($this->user)->create();
    $thing = Thing::factory()->for($this->user)->create(['device_id' => $device->id]);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->call('detachDevice');

    expect($thing->fresh()->device_id)->toBeNull();
});

// --- Tags ---

it('can add a tag to a thing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->set('tagKey', 'env')
        ->set('tagValue', 'production')
        ->call('addTag');

    $tag = $thing->tags()->where('key', 'env')->first();

    expect($tag)->not->toBeNull();
    expect($tag->value)->toBe('production');
});

it('prevents duplicate tag keys', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    ThingTag::factory()->for($thing)->create(['key' => 'env', 'value' => 'staging']);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->set('tagKey', 'env')
        ->set('tagValue', 'production')
        ->call('addTag')
        ->assertHasErrors('tagKey');
});

it('validates tag key is required', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->set('tagKey', '')
        ->set('tagValue', 'value')
        ->call('addTag')
        ->assertHasErrors('tagKey');
});

it('validates tag value is required', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->set('tagKey', 'key')
        ->set('tagValue', '')
        ->call('addTag')
        ->assertHasErrors('tagValue');
});

it('can delete a tag', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $tag = ThingTag::factory()->for($thing)->create(['key' => 'env', 'value' => 'staging']);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->call('deleteTag', $tag->id);

    expect(ThingTag::find($tag->id))->toBeNull();
});

it('shows existing tags on the show page', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    ThingTag::factory()->for($thing)->create(['key' => 'location', 'value' => 'office']);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->assertSee('location')
        ->assertSee('office');
});

// --- Edit ---

it('shows the edit form with current values', function (): void {
    $thing = Thing::factory()->for($this->user)->create(['name' => 'Old Name', 'timezone' => 'Europe/Berlin']);

    Livewire::test(Things\Edit::class, ['thing' => $thing])
        ->assertSet('name', 'Old Name')
        ->assertSet('timezone', 'Europe/Berlin')
        ->assertSuccessful();
});

it('can update a thing', function (): void {
    $thing = Thing::factory()->for($this->user)->create(['name' => 'Old Name']);

    Livewire::test(Things\Edit::class, ['thing' => $thing])
        ->set('name', 'New Name')
        ->set('timezone', 'America/New_York')
        ->call('updateThing')
        ->assertRedirect(route('things.show', $thing));

    expect($thing->fresh())
        ->name->toBe('New Name')
        ->timezone->toBe('America/New_York');
});

it('can reassign a device when editing', function (): void {
    $device = Device::factory()->for($this->user)->create();
    $thing = Thing::factory()->for($this->user)->create(['device_id' => null]);

    Livewire::test(Things\Edit::class, ['thing' => $thing])
        ->set('device_id', $device->id)
        ->call('updateThing')
        ->assertRedirect(route('things.show', $thing));

    expect($thing->fresh()->device_id)->toBe($device->id);
});

it('validates name when editing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Edit::class, ['thing' => $thing])
        ->set('name', '')
        ->call('updateThing')
        ->assertHasErrors('name');
});

it('validates timezone when editing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Edit::class, ['thing' => $thing])
        ->set('timezone', 'Not/Real')
        ->call('updateThing')
        ->assertHasErrors('timezone');
});

it('returns 403 when editing another user\'s thing', function (): void {
    $otherThing = Thing::factory()->create();

    Livewire::test(Things\Edit::class, ['thing' => $otherThing])
        ->assertForbidden();
});

it('includes currently assigned device in available devices on edit', function (): void {
    $device = Device::factory()->for($this->user)->create(['name' => 'Current Device']);
    $thing = Thing::factory()->for($this->user)->create(['device_id' => $device->id]);

    Livewire::test(Things\Edit::class, ['thing' => $thing])
        ->assertSee('Current Device');
});
