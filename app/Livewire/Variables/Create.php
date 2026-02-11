<?php declare(strict_types=1);

namespace App\Livewire\Variables;

use App\Enums\CloudVariableType;
use App\Enums\VariablePermission;
use App\Enums\VariableUpdatePolicy;
use App\Models\Thing;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Cloud Variable')]
final class Create extends Component
{
    public Thing $thing;

    public string $name = '';

    public string $type = '';

    public string $permission = '';

    public string $update_policy = '';

    public ?string $update_parameter = null;

    public ?string $min_value = null;

    public ?string $max_value = null;

    public bool $persist = false;

    public function mount(Thing $thing): void
    {
        abort_unless($thing->user_id === Auth::id(), 403);

        $this->thing = $thing;
        $this->type = CloudVariableType::Float->value;
        $this->permission = VariablePermission::ReadWrite->value;
        $this->update_policy = VariableUpdatePolicy::OnChange->value;
    }

    #[Computed]
    public function variableName(): string
    {
        return Str::snake($this->name);
    }

    #[Computed]
    public function declaration(): string
    {
        $variableName = $this->variableName();

        if ($variableName === '' || $this->type === '') {
            return '';
        }

        $type = CloudVariableType::tryFrom($this->type);

        if (! $type) {
            return '';
        }

        return $type->declarationType().' '.$variableName.';';
    }

    public function createVariable(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', 'string', new Enum(CloudVariableType::class)],
            'permission' => ['required', 'string', new Enum(VariablePermission::class)],
            'update_policy' => ['required', 'string', new Enum(VariableUpdatePolicy::class)],
            'update_parameter' => ['nullable', 'numeric', 'min:0'],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'persist' => ['boolean'],
        ]);

        $variableName = Str::snake($this->name);

        $exists = $this->thing->cloudVariables()
            ->where('variable_name', $variableName)
            ->exists();

        if ($exists) {
            $this->addError('name', 'A variable with this name already exists on this thing.');

            return;
        }

        if ($this->min_value !== null && $this->max_value !== null && (float) $this->max_value < (float) $this->min_value) {
            $this->addError('max_value', 'The maximum value must be greater than or equal to the minimum value.');

            return;
        }

        $this->thing->cloudVariables()->create([
            'name' => $this->name,
            'variable_name' => $variableName,
            'type' => $this->type,
            'permission' => $this->permission,
            'update_policy' => $this->update_policy,
            'update_parameter' => $this->update_parameter,
            'min_value' => $this->min_value,
            'max_value' => $this->max_value,
            'persist' => $this->persist,
        ]);

        Flux::toast(text: 'Your new cloud variable has been created.', heading: 'Variable created', variant: 'success');

        $this->redirect(route('things.show', $this->thing), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.variables.create', [
            'types' => CloudVariableType::cases(),
            'permissions' => VariablePermission::cases(),
            'policies' => VariableUpdatePolicy::cases(),
        ]);
    }
}
