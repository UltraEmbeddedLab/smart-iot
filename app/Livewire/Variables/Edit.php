<?php declare(strict_types=1);

namespace App\Livewire\Variables;

use App\Enums\VariablePermission;
use App\Enums\VariableUpdatePolicy;
use App\Models\CloudVariable;
use App\Models\Thing;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Cloud Variable')]
final class Edit extends Component
{
    public Thing $thing;

    public CloudVariable $variable;

    public string $name = '';

    public string $permission = '';

    public string $update_policy = '';

    public ?string $update_parameter = null;

    public ?string $min_value = null;

    public ?string $max_value = null;

    public bool $persist = false;

    public function mount(Thing $thing, CloudVariable $variable): void
    {
        abort_unless($thing->user_id === Auth::id(), 403);
        abort_unless($variable->thing_id === $thing->id, 404);

        $this->thing = $thing;
        $this->variable = $variable;
        $this->name = $variable->name;
        $this->permission = $variable->permission->value;
        $this->update_policy = $variable->update_policy->value;
        $this->update_parameter = $variable->update_parameter;
        $this->min_value = $variable->min_value;
        $this->max_value = $variable->max_value;
        $this->persist = $variable->persist;
    }

    public function updateVariable(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'permission' => ['required', 'string', new Enum(VariablePermission::class)],
            'update_policy' => ['required', 'string', new Enum(VariableUpdatePolicy::class)],
            'update_parameter' => ['nullable', 'numeric', 'min:0'],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'persist' => ['boolean'],
        ]);

        if ($this->min_value !== null && $this->max_value !== null && (float) $this->max_value < (float) $this->min_value) {
            $this->addError('max_value', 'The maximum value must be greater than or equal to the minimum value.');

            return;
        }

        $this->variable->update([
            'name' => $this->name,
            'permission' => $this->permission,
            'update_policy' => $this->update_policy,
            'update_parameter' => $this->update_parameter,
            'min_value' => $this->min_value,
            'max_value' => $this->max_value,
            'persist' => $this->persist,
        ]);

        Flux::toast(text: 'The cloud variable has been updated.', heading: 'Variable updated', variant: 'success');

        $this->redirect(route('things.show', $this->thing), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.variables.edit', [
            'permissions' => VariablePermission::cases(),
            'policies' => VariableUpdatePolicy::cases(),
        ]);
    }
}
