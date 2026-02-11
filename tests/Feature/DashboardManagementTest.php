<?php declare(strict_types=1);

use App\Enums\VariablePermission;
use App\Enums\WidgetType;
use App\Jobs\PublishMqttMessage;
use App\Livewire\Dashboards;
use App\Models\CloudVariable;
use App\Models\Dashboard;
use App\Models\Thing;
use App\Models\User;
use App\Models\Widget;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// --- Index ---

it('shows the dashboards index page', function (): void {
    Dashboard::factory()->for($this->user)->create(['name' => 'My Dashboard']);

    Livewire::test(Dashboards\Index::class)
        ->assertSee('My Dashboard')
        ->assertSuccessful();
});

it('only shows dashboards belonging to the authenticated user', function (): void {
    Dashboard::factory()->for($this->user)->create(['name' => 'Own Dashboard']);
    Dashboard::factory()->create(['name' => 'Other Dashboard']);

    Livewire::test(Dashboards\Index::class)
        ->assertSee('Own Dashboard')
        ->assertDontSee('Other Dashboard');
});

it('can search dashboards by name', function (): void {
    Dashboard::factory()->for($this->user)->create(['name' => 'Kitchen Monitor']);
    Dashboard::factory()->for($this->user)->create(['name' => 'Garden Overview']);

    Livewire::test(Dashboards\Index::class)
        ->set('search', 'Kitchen')
        ->assertSee('Kitchen Monitor')
        ->assertDontSee('Garden Overview');
});

it('can sort dashboards', function (): void {
    Dashboard::factory()->for($this->user)->create(['name' => 'Alpha', 'created_at' => now()->subDay()]);
    Dashboard::factory()->for($this->user)->create(['name' => 'Zeta', 'created_at' => now()]);

    Livewire::test(Dashboards\Index::class)
        ->call('sort', 'name')
        ->assertSeeInOrder(['Alpha', 'Zeta']);
});

it('can delete a dashboard from index', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create(['name' => 'To Delete']);

    Livewire::test(Dashboards\Index::class)
        ->call('deleteDashboard', $dashboard->id);

    expect(Dashboard::find($dashboard->id))->toBeNull();
});

// --- Create ---

it('shows the create dashboard form', function (): void {
    Livewire::test(Dashboards\Create::class)
        ->assertSee('Create Dashboard')
        ->assertSuccessful();
});

it('can create a dashboard', function (): void {
    Livewire::test(Dashboards\Create::class)
        ->set('name', 'New Dashboard')
        ->call('createDashboard');

    $dashboard = Dashboard::query()->where('name', 'New Dashboard')->first();

    expect($dashboard)
        ->not->toBeNull()
        ->user_id->toBe($this->user->id);
});

it('validates name is required when creating a dashboard', function (): void {
    Livewire::test(Dashboards\Create::class)
        ->set('name', '')
        ->call('createDashboard')
        ->assertHasErrors('name');
});

// --- Show ---

it('shows dashboard with widgets', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create(['last_value' => ['value' => 42]]);
    $dashboard = Dashboard::factory()->for($this->user)->create(['name' => 'Test Dashboard']);
    Widget::factory()->for($dashboard)->ofType(WidgetType::Value)->create([
        'name' => 'Temp Widget',
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Dashboards\Show::class, ['dashboard' => $dashboard])
        ->assertSee('Test Dashboard')
        ->assertSee('Temp Widget')
        ->assertSuccessful();
});

it('returns 403 for another user dashboard', function (): void {
    $otherDashboard = Dashboard::factory()->create();

    Livewire::test(Dashboards\Show::class, ['dashboard' => $otherDashboard])
        ->assertForbidden();
});

it('shows empty state when no widgets', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create();

    Livewire::test(Dashboards\Show::class, ['dashboard' => $dashboard])
        ->assertSee('No widgets yet')
        ->assertSuccessful();
});

it('can toggle a switch widget', function (): void {
    Queue::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'permission' => VariablePermission::ReadWrite,
        'last_value' => ['value' => false],
    ]);
    $dashboard = Dashboard::factory()->for($this->user)->create();
    $widget = Widget::factory()->for($dashboard)->ofType(WidgetType::Switch)->create([
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Dashboards\Show::class, ['dashboard' => $dashboard])
        ->call('toggleSwitch', $widget->id);

    expect($variable->fresh()->last_value)->toBe(['value' => true]);

    Queue::assertPushed(PublishMqttMessage::class);
});

it('can update a slider widget', function (): void {
    Queue::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'permission' => VariablePermission::ReadWrite,
        'last_value' => ['value' => 50],
        'min_value' => 0,
        'max_value' => 100,
    ]);
    $dashboard = Dashboard::factory()->for($this->user)->create();
    $widget = Widget::factory()->for($dashboard)->ofType(WidgetType::Slider)->create([
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Dashboards\Show::class, ['dashboard' => $dashboard])
        ->call('updateSlider', $widget->id, 75.0);

    expect($variable->fresh()->last_value['value'])->toBe(75);

    Queue::assertPushed(PublishMqttMessage::class);
});

// --- Edit ---

it('shows edit form with current values', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create(['name' => 'Old Name']);

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->assertSet('name', 'Old Name')
        ->assertSuccessful();
});

it('can update dashboard name', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create(['name' => 'Old Name']);

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->set('name', 'New Name')
        ->call('updateDashboard')
        ->assertRedirect(route('dashboards.show', $dashboard));

    expect($dashboard->fresh()->name)->toBe('New Name');
});

it('can add a widget to a dashboard', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    $dashboard = Dashboard::factory()->for($this->user)->create();

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->set('widgetType', WidgetType::Value->value)
        ->set('widgetName', 'Temperature')
        ->set('cloudVariableId', $variable->id)
        ->call('addWidget');

    expect($dashboard->widgets()->count())->toBe(1);
    expect($dashboard->widgets()->first())
        ->name->toBe('Temperature')
        ->type->toBe(WidgetType::Value)
        ->cloud_variable_id->toBe($variable->id);
});

it('can delete a widget from a dashboard', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create();
    $widget = Widget::factory()->for($dashboard)->withoutVariable()->create();

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->call('deleteWidget', $widget->id);

    expect(Widget::find($widget->id))->toBeNull();
});

it('validates widget type when adding', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create();

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->set('widgetType', 'invalid_type')
        ->set('widgetName', 'Test Widget')
        ->call('addWidget')
        ->assertHasErrors('widgetType');
});

it('returns 403 when editing another user dashboard', function (): void {
    $otherDashboard = Dashboard::factory()->create();

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $otherDashboard])
        ->assertForbidden();
});

it('prevents linking another user variable to widget', function (): void {
    $otherThing = Thing::factory()->create();
    $otherVariable = CloudVariable::factory()->for($otherThing)->create();
    $dashboard = Dashboard::factory()->for($this->user)->create();

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->set('widgetType', WidgetType::Value->value)
        ->set('widgetName', 'Sneaky Widget')
        ->set('cloudVariableId', $otherVariable->id)
        ->call('addWidget')
        ->assertHasErrors('cloudVariableId');
});

it('deletes widgets when dashboard is deleted (cascade)', function (): void {
    $dashboard = Dashboard::factory()->for($this->user)->create();
    $widget = Widget::factory()->for($dashboard)->withoutVariable()->create();

    Livewire::test(Dashboards\Edit::class, ['dashboard' => $dashboard])
        ->call('deleteDashboard')
        ->assertRedirectToRoute('dashboards.index');

    expect(Dashboard::find($dashboard->id))->toBeNull();
    expect(Widget::find($widget->id))->toBeNull();
});
