<?php declare(strict_types=1);

namespace App\Livewire\Triggers;

use App\Enums\TriggerActionType;
use App\Enums\TriggerOperator;
use App\Models\CloudVariable;
use App\Models\Trigger;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Trigger')]
final class Create extends Component
{
    public string $name = '';

    public ?int $cloudVariableId = null;

    public string $operator = '';

    public string $value = '';

    public string $actionType = '';

    /** @var array<string, mixed> */
    public array $actionConfig = [];

    public int $cooldownSeconds = 0;

    public function createTrigger(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'cloudVariableId' => ['required', 'integer', 'exists:cloud_variables,id'],
            'operator' => ['required', 'string', new Enum(TriggerOperator::class)],
            'value' => ['required', 'string', 'max:255'],
            'actionType' => ['required', 'string', new Enum(TriggerActionType::class)],
            'actionConfig' => ['required', 'array'],
            'actionConfig.email' => ['required_if:actionType,email', 'nullable', 'email'],
            'actionConfig.url' => ['required_if:actionType,webhook', 'nullable', 'url'],
            'cooldownSeconds' => ['integer', 'min:0'],
        ]);

        $variableOwnedByUser = CloudVariable::query()
            ->where('id', $this->cloudVariableId)
            ->whereHas('thing', fn ($query) => $query->where('user_id', Auth::id()))
            ->exists();

        if (! $variableOwnedByUser) {
            $this->addError('cloudVariableId', 'The selected variable does not belong to you.');

            return;
        }

        Trigger::query()->create([
            'user_id' => Auth::id(),
            'cloud_variable_id' => $this->cloudVariableId,
            'name' => $this->name,
            'operator' => $this->operator,
            'value' => $this->value,
            'action_type' => $this->actionType,
            'action_config' => $this->actionConfig,
            'cooldown_seconds' => $this->cooldownSeconds,
        ]);

        Flux::toast(text: 'Your new trigger has been created.', heading: 'Trigger created', variant: 'success');

        $this->redirect(route('triggers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.triggers.create');
    }

    /**
     * @return Collection<int, CloudVariable>
     */
    #[Computed]
    public function availableVariables(): Collection
    {
        return CloudVariable::query()
            ->whereHas('thing', fn ($query) => $query->where('user_id', Auth::id()))
            ->with('thing')
            ->orderBy('name')
            ->get();
    }
}
