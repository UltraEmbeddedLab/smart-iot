<?php declare(strict_types=1);

use App\Enums\CloudVariableType;
use App\Enums\VariablePermission;
use App\Enums\VariableUpdatePolicy;
use App\Livewire\Things;
use App\Livewire\Variables;
use App\Models\CloudVariable;
use App\Models\Thing;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// --- Create ---

it('shows the create variable form', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->assertSee('Create Cloud Variable')
        ->assertSuccessful();
});

it('can create a cloud variable', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Room Temperature')
        ->set('type', CloudVariableType::Temperature->value)
        ->set('permission', VariablePermission::ReadOnly->value)
        ->set('update_policy', VariableUpdatePolicy::OnChange->value)
        ->call('createVariable')
        ->assertRedirectToRoute('things.show', $thing);

    $variable = $thing->cloudVariables()->first();

    expect($variable)->not->toBeNull()
        ->name->toBe('Room Temperature')
        ->variable_name->toBe('room_temperature')
        ->type->toBe(CloudVariableType::Temperature)
        ->permission->toBe(VariablePermission::ReadOnly);
});

it('auto-generates variable_name from name', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Living Room Humidity')
        ->set('type', CloudVariableType::Humidity->value)
        ->call('createVariable')
        ->assertRedirectToRoute('things.show', $thing);

    expect($thing->cloudVariables()->first())
        ->variable_name->toBe('living_room_humidity');
});

it('validates required fields when creating', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', '')
        ->call('createVariable')
        ->assertHasErrors('name');
});

it('validates name minimum length when creating', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'A')
        ->call('createVariable')
        ->assertHasErrors('name');
});

it('validates enum values when creating', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Test Variable')
        ->set('type', 'invalid_type')
        ->call('createVariable')
        ->assertHasErrors('type');
});

it('validates unique variable_name per thing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    CloudVariable::factory()->for($thing)->create(['variable_name' => 'room_temp', 'name' => 'Room Temp']);

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Room Temp')
        ->call('createVariable')
        ->assertHasErrors('name');
});

it('allows same variable_name on different things', function (): void {
    $thing1 = Thing::factory()->for($this->user)->create();
    $thing2 = Thing::factory()->for($this->user)->create();
    CloudVariable::factory()->for($thing1)->create(['variable_name' => 'temperature', 'name' => 'Temperature']);

    Livewire::test(Variables\Create::class, ['thing' => $thing2])
        ->set('name', 'Temperature')
        ->call('createVariable')
        ->assertRedirectToRoute('things.show', $thing2);

    expect($thing2->cloudVariables()->count())->toBe(1);
});

it('validates max_value is greater than min_value', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Test Variable')
        ->set('min_value', '100')
        ->set('max_value', '50')
        ->call('createVariable')
        ->assertHasErrors('max_value');
});

it('can create a variable with update parameter', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Periodic Sensor')
        ->set('update_policy', VariableUpdatePolicy::Periodically->value)
        ->set('update_parameter', '60')
        ->call('createVariable')
        ->assertRedirectToRoute('things.show', $thing);

    expect($thing->cloudVariables()->first())
        ->update_policy->toBe(VariableUpdatePolicy::Periodically)
        ->update_parameter->toBe('60.00');
});

it('can create a variable with persist enabled', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Persisted Value')
        ->set('persist', true)
        ->call('createVariable')
        ->assertRedirectToRoute('things.show', $thing);

    expect($thing->cloudVariables()->first())
        ->persist->toBeTrue();
});

it('returns 403 when creating variable for another user\'s thing', function (): void {
    $otherThing = Thing::factory()->create();

    Livewire::test(Variables\Create::class, ['thing' => $otherThing])
        ->assertForbidden();
});

// --- Declaration ---

it('generates correct declaration for each type', function (CloudVariableType $type, string $expectedPrefix): void {
    $variable = CloudVariable::factory()->ofType($type)->create([
        'variable_name' => 'test_var',
    ]);

    expect($variable->declaration)->toBe($expectedPrefix.' test_var;');
})->with([
    'Int' => [CloudVariableType::Int, 'int'],
    'Float' => [CloudVariableType::Float, 'float'],
    'Boolean' => [CloudVariableType::Boolean, 'bool'],
    'String' => [CloudVariableType::String, 'String'],
    'Temperature' => [CloudVariableType::Temperature, 'CloudTemperature'],
    'Humidity' => [CloudVariableType::Humidity, 'CloudHumidity'],
    'Luminosity' => [CloudVariableType::Luminosity, 'CloudLuminosity'],
    'Percentage' => [CloudVariableType::Percentage, 'CloudPercentage'],
    'Voltage' => [CloudVariableType::Voltage, 'CloudVoltage'],
    'Current' => [CloudVariableType::Current, 'CloudCurrent'],
    'Power' => [CloudVariableType::Power, 'CloudPower'],
    'Pressure' => [CloudVariableType::Pressure, 'CloudPressure'],
    'Speed' => [CloudVariableType::Speed, 'CloudSpeed'],
    'Location' => [CloudVariableType::Location, 'CloudLocation'],
    'Color' => [CloudVariableType::Color, 'CloudColor'],
    'Switch' => [CloudVariableType::Switch, 'CloudSwitch'],
    'DimmedLight' => [CloudVariableType::DimmedLight, 'CloudDimmedLight'],
]);

it('generates declaration preview in create form', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Variables\Create::class, ['thing' => $thing])
        ->set('name', 'Room Temperature')
        ->set('type', CloudVariableType::Temperature->value)
        ->assertSee('CloudTemperature room_temperature;');
});

// --- Show ---

it('shows cloud variables on the thing show page', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    CloudVariable::factory()->for($thing)->create([
        'name' => 'Room Temp',
        'variable_name' => 'room_temp',
        'type' => CloudVariableType::Temperature,
    ]);

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->assertSee('Room Temp')
        ->assertSee('Temperature');
});

it('shows empty state when no variables exist', function (): void {
    $thing = Thing::factory()->for($this->user)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->assertSee('No cloud variables have been added yet.');
});

it('can delete a variable from the show page', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->call('deleteVariable', $variable->id);

    expect(CloudVariable::find($variable->id))->toBeNull();
});

it('cannot delete another thing\'s variable', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $otherThing = Thing::factory()->create();
    $variable = CloudVariable::factory()->for($otherThing)->create();

    Livewire::test(Things\Show::class, ['thing' => $thing])
        ->call('deleteVariable', $variable->id);

    expect(CloudVariable::find($variable->id))->not->toBeNull();
});

// --- Edit ---

it('shows the edit form with current values', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'name' => 'Sensor Value',
        'permission' => VariablePermission::ReadOnly,
    ]);

    Livewire::test(Variables\Edit::class, ['thing' => $thing, 'variable' => $variable])
        ->assertSet('name', 'Sensor Value')
        ->assertSet('permission', VariablePermission::ReadOnly->value)
        ->assertSuccessful();
});

it('can update variable properties', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'name' => 'Old Name',
        'permission' => VariablePermission::ReadOnly,
    ]);

    Livewire::test(Variables\Edit::class, ['thing' => $thing, 'variable' => $variable])
        ->set('name', 'New Name')
        ->set('permission', VariablePermission::ReadWrite->value)
        ->set('persist', true)
        ->call('updateVariable')
        ->assertRedirect(route('things.show', $thing));

    expect($variable->fresh())
        ->name->toBe('New Name')
        ->permission->toBe(VariablePermission::ReadWrite)
        ->persist->toBeTrue();
});

it('does not change type or variable_name on edit', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'name' => 'Test',
        'variable_name' => 'test',
        'type' => CloudVariableType::Temperature,
    ]);

    Livewire::test(Variables\Edit::class, ['thing' => $thing, 'variable' => $variable])
        ->set('name', 'Updated Name')
        ->call('updateVariable')
        ->assertRedirect(route('things.show', $thing));

    expect($variable->fresh())
        ->variable_name->toBe('test')
        ->type->toBe(CloudVariableType::Temperature);
});

it('validates name when editing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    Livewire::test(Variables\Edit::class, ['thing' => $thing, 'variable' => $variable])
        ->set('name', '')
        ->call('updateVariable')
        ->assertHasErrors('name');
});

it('validates max_value when editing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    Livewire::test(Variables\Edit::class, ['thing' => $thing, 'variable' => $variable])
        ->set('min_value', '100')
        ->set('max_value', '50')
        ->call('updateVariable')
        ->assertHasErrors('max_value');
});

it('returns 403 when editing variable for another user\'s thing', function (): void {
    $otherThing = Thing::factory()->create();
    $variable = CloudVariable::factory()->for($otherThing)->create();

    Livewire::test(Variables\Edit::class, ['thing' => $otherThing, 'variable' => $variable])
        ->assertForbidden();
});

it('returns 404 when variable does not belong to thing', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $otherThing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($otherThing)->create();

    Livewire::test(Variables\Edit::class, ['thing' => $thing, 'variable' => $variable])
        ->assertNotFound();
});

// --- Cascade Delete ---

it('deletes cloud variables when thing is deleted', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    $thing->delete();

    expect(CloudVariable::find($variable->id))->toBeNull();
});
