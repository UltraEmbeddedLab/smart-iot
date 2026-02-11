<?php declare(strict_types=1);

use App\Enums\TriggerActionType;
use App\Enums\TriggerOperator;
use App\Events\CloudVariableUpdated;
use App\Jobs\ExecuteTriggerAction;
use App\Listeners\EvaluateCloudTriggers;
use App\Livewire\Triggers;
use App\Mail\TriggerAlertMail;
use App\Models\CloudVariable;
use App\Models\Thing;
use App\Models\Trigger;
use App\Models\User;
use App\Services\TriggerEvaluator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// --- Index ---

it('shows the triggers index page', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    Trigger::factory()->for($this->user)->create([
        'name' => 'My Trigger',
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Triggers\Index::class)
        ->assertSee('My Trigger')
        ->assertSuccessful();
});

it('only shows triggers belonging to the authenticated user', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    Trigger::factory()->for($this->user)->create([
        'name' => 'Own Trigger',
        'cloud_variable_id' => $variable->id,
    ]);

    $otherThing = Thing::factory()->create();
    $otherVariable = CloudVariable::factory()->for($otherThing)->create();
    Trigger::factory()->create([
        'name' => 'Other Trigger',
        'cloud_variable_id' => $otherVariable->id,
    ]);

    Livewire::test(Triggers\Index::class)
        ->assertSee('Own Trigger')
        ->assertDontSee('Other Trigger');
});

it('can search triggers by name', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    Trigger::factory()->for($this->user)->create([
        'name' => 'Temperature Alert',
        'cloud_variable_id' => $variable->id,
    ]);
    Trigger::factory()->for($this->user)->create([
        'name' => 'Humidity Warning',
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Triggers\Index::class)
        ->set('search', 'Temperature')
        ->assertSee('Temperature Alert')
        ->assertDontSee('Humidity Warning');
});

it('can toggle trigger active status', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    $trigger = Trigger::factory()->for($this->user)->active()->create([
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Triggers\Index::class)
        ->call('toggleActive', $trigger->id);

    expect($trigger->fresh()->is_active)->toBeFalse();
});

it('can delete a trigger from index', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    $trigger = Trigger::factory()->for($this->user)->create([
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Triggers\Index::class)
        ->call('deleteTrigger', $trigger->id);

    expect(Trigger::find($trigger->id))->toBeNull();
});

// --- Create ---

it('shows the create trigger form', function (): void {
    Livewire::test(Triggers\Create::class)
        ->assertSee('Create Trigger')
        ->assertSuccessful();
});

it('can create an email trigger', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    Livewire::test(Triggers\Create::class)
        ->set('name', 'High Temp Alert')
        ->set('cloudVariableId', $variable->id)
        ->set('operator', TriggerOperator::GreaterThan->value)
        ->set('value', '100')
        ->set('actionType', TriggerActionType::Email->value)
        ->set('actionConfig.email', 'test@example.com')
        ->set('cooldownSeconds', 60)
        ->call('createTrigger')
        ->assertRedirect(route('triggers.index'));

    $trigger = Trigger::query()->where('name', 'High Temp Alert')->first();

    expect($trigger)
        ->not->toBeNull()
        ->user_id->toBe($this->user->id)
        ->operator->toBe(TriggerOperator::GreaterThan)
        ->action_type->toBe(TriggerActionType::Email)
        ->action_config->toBe(['email' => 'test@example.com'])
        ->cooldown_seconds->toBe(60);
});

it('can create a webhook trigger', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    Livewire::test(Triggers\Create::class)
        ->set('name', 'Webhook Trigger')
        ->set('cloudVariableId', $variable->id)
        ->set('operator', TriggerOperator::LessThan->value)
        ->set('value', '10')
        ->set('actionType', TriggerActionType::Webhook->value)
        ->set('actionConfig.url', 'https://example.com/webhook')
        ->call('createTrigger')
        ->assertRedirect(route('triggers.index'));

    $trigger = Trigger::query()->where('name', 'Webhook Trigger')->first();

    expect($trigger)
        ->not->toBeNull()
        ->action_type->toBe(TriggerActionType::Webhook)
        ->action_config->toBe(['url' => 'https://example.com/webhook']);
});

it('validates name is required when creating a trigger', function (): void {
    Livewire::test(Triggers\Create::class)
        ->set('name', '')
        ->call('createTrigger')
        ->assertHasErrors('name');
});

it('validates operator is a valid enum', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();

    Livewire::test(Triggers\Create::class)
        ->set('name', 'Test')
        ->set('cloudVariableId', $variable->id)
        ->set('operator', 'invalid_operator')
        ->set('value', '100')
        ->set('actionType', TriggerActionType::Email->value)
        ->set('actionConfig.email', 'test@example.com')
        ->call('createTrigger')
        ->assertHasErrors('operator');
});

it('prevents linking another user variable to trigger', function (): void {
    $otherThing = Thing::factory()->create();
    $otherVariable = CloudVariable::factory()->for($otherThing)->create();

    Livewire::test(Triggers\Create::class)
        ->set('name', 'Sneaky Trigger')
        ->set('cloudVariableId', $otherVariable->id)
        ->set('operator', TriggerOperator::Equals->value)
        ->set('value', '50')
        ->set('actionType', TriggerActionType::Email->value)
        ->set('actionConfig.email', 'test@example.com')
        ->call('createTrigger')
        ->assertHasErrors('cloudVariableId');
});

// --- Edit ---

it('shows edit form with current values', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    $trigger = Trigger::factory()->for($this->user)->create([
        'name' => 'Old Name',
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Triggers\Edit::class, ['trigger' => $trigger])
        ->assertSet('name', 'Old Name')
        ->assertSuccessful();
});

it('can update a trigger', function (): void {
    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create();
    $trigger = Trigger::factory()->for($this->user)->create([
        'name' => 'Old Name',
        'cloud_variable_id' => $variable->id,
    ]);

    Livewire::test(Triggers\Edit::class, ['trigger' => $trigger])
        ->set('name', 'New Name')
        ->set('operator', TriggerOperator::LessOrEqual->value)
        ->set('value', '50')
        ->call('updateTrigger')
        ->assertRedirect(route('triggers.index'));

    expect($trigger->fresh())
        ->name->toBe('New Name')
        ->operator->toBe(TriggerOperator::LessOrEqual)
        ->value->toBe('50');
});

it('returns 403 when editing another user trigger', function (): void {
    $otherThing = Thing::factory()->create();
    $otherVariable = CloudVariable::factory()->for($otherThing)->create();
    $otherTrigger = Trigger::factory()->create([
        'cloud_variable_id' => $otherVariable->id,
    ]);

    Livewire::test(Triggers\Edit::class, ['trigger' => $otherTrigger])
        ->assertForbidden();
});

// --- TriggerEvaluator ---

it('evaluates GreaterThan correctly', function (): void {
    $evaluator = new TriggerEvaluator;
    $trigger = Trigger::factory()->ofOperator(TriggerOperator::GreaterThan)->make(['value' => '100']);

    expect($evaluator->evaluate($trigger, 150))->toBeTrue();
    expect($evaluator->evaluate($trigger, 50))->toBeFalse();
    expect($evaluator->evaluate($trigger, 100))->toBeFalse();
});

it('evaluates LessThan correctly', function (): void {
    $evaluator = new TriggerEvaluator;
    $trigger = Trigger::factory()->ofOperator(TriggerOperator::LessThan)->make(['value' => '100']);

    expect($evaluator->evaluate($trigger, 50))->toBeTrue();
    expect($evaluator->evaluate($trigger, 150))->toBeFalse();
    expect($evaluator->evaluate($trigger, 100))->toBeFalse();
});

it('evaluates Equals correctly', function (): void {
    $evaluator = new TriggerEvaluator;
    $trigger = Trigger::factory()->ofOperator(TriggerOperator::Equals)->make(['value' => '42']);

    expect($evaluator->evaluate($trigger, 42))->toBeTrue();
    expect($evaluator->evaluate($trigger, 43))->toBeFalse();
});

it('evaluates NotEquals correctly', function (): void {
    $evaluator = new TriggerEvaluator;
    $trigger = Trigger::factory()->ofOperator(TriggerOperator::NotEquals)->make(['value' => '42']);

    expect($evaluator->evaluate($trigger, 43))->toBeTrue();
    expect($evaluator->evaluate($trigger, 42))->toBeFalse();
});

// --- EvaluateCloudTriggers Listener ---

it('dispatches job when condition is met', function (): void {
    Queue::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'last_value' => ['value' => 150],
    ]);
    Trigger::factory()->for($this->user)->active()->create([
        'cloud_variable_id' => $variable->id,
        'operator' => TriggerOperator::GreaterThan,
        'value' => '100',
    ]);

    $event = new CloudVariableUpdated($variable, ['value' => 90], ['value' => 150]);
    app(EvaluateCloudTriggers::class)->handle($event);

    Queue::assertPushed(ExecuteTriggerAction::class);
});

it('does not dispatch when condition is not met', function (): void {
    Queue::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'last_value' => ['value' => 50],
    ]);
    Trigger::factory()->for($this->user)->active()->create([
        'cloud_variable_id' => $variable->id,
        'operator' => TriggerOperator::GreaterThan,
        'value' => '100',
    ]);

    $event = new CloudVariableUpdated($variable, ['value' => 40], ['value' => 50]);
    app(EvaluateCloudTriggers::class)->handle($event);

    Queue::assertNotPushed(ExecuteTriggerAction::class);
});

it('respects cooldown period', function (): void {
    Queue::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'last_value' => ['value' => 150],
    ]);
    Trigger::factory()->for($this->user)->active()->withCooldown(3600)->create([
        'cloud_variable_id' => $variable->id,
        'operator' => TriggerOperator::GreaterThan,
        'value' => '100',
        'last_triggered_at' => now()->subMinutes(5),
    ]);

    $event = new CloudVariableUpdated($variable, ['value' => 140], ['value' => 150]);
    app(EvaluateCloudTriggers::class)->handle($event);

    Queue::assertNotPushed(ExecuteTriggerAction::class);
});

it('skips inactive triggers', function (): void {
    Queue::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'last_value' => ['value' => 150],
    ]);
    Trigger::factory()->for($this->user)->inactive()->create([
        'cloud_variable_id' => $variable->id,
        'operator' => TriggerOperator::GreaterThan,
        'value' => '100',
    ]);

    $event = new CloudVariableUpdated($variable, ['value' => 90], ['value' => 150]);
    app(EvaluateCloudTriggers::class)->handle($event);

    Queue::assertNotPushed(ExecuteTriggerAction::class);
});

// --- ExecuteTriggerAction Job ---

it('sends email when trigger action is email', function (): void {
    Mail::fake();

    $thing = Thing::factory()->for($this->user)->create();
    $variable = CloudVariable::factory()->for($thing)->create([
        'last_value' => ['value' => 150],
    ]);
    $trigger = Trigger::factory()->for($this->user)->create([
        'cloud_variable_id' => $variable->id,
        'action_type' => TriggerActionType::Email,
        'action_config' => ['email' => 'alert@example.com'],
        'last_triggered_at' => now(),
    ]);

    (new ExecuteTriggerAction($trigger))->handle();

    Mail::assertSent(TriggerAlertMail::class, function (TriggerAlertMail $mail) {
        return $mail->hasTo('alert@example.com');
    });
});
