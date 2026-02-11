<?php declare(strict_types=1);

namespace App\Livewire\Dashboards;

use App\Enums\WidgetType;
use App\Models\CloudVariable;
use App\Models\Dashboard;
use App\Models\Widget;
use Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit Dashboard')]
final class Edit extends Component
{
    public Dashboard $dashboard;

    public string $name = '';

    public string $widgetType = '';

    public string $widgetName = '';

    public ?int $cloudVariableId = null;

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->user_id === Auth::id(), 403);

        $this->dashboard = $dashboard;
        $this->name = $dashboard->name;
    }

    public function updateDashboard(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $this->dashboard->update([
            'name' => $this->name,
        ]);

        Flux::toast(text: 'The dashboard has been updated.', heading: 'Dashboard updated', variant: 'success');

        $this->redirect(route('dashboards.show', $this->dashboard), navigate: true);
    }

    public function addWidget(): void
    {
        $this->validate([
            'widgetType' => ['required', 'string', new Enum(WidgetType::class)],
            'widgetName' => ['required', 'string', 'min:2', 'max:255'],
            'cloudVariableId' => ['nullable', 'integer', 'exists:cloud_variables,id'],
        ]);

        if ($this->cloudVariableId) {
            $variableOwnedByUser = CloudVariable::query()
                ->where('id', $this->cloudVariableId)
                ->whereHas('thing', fn ($query) => $query->where('user_id', Auth::id()))
                ->exists();

            if (! $variableOwnedByUser) {
                $this->addError('cloudVariableId', 'The selected variable does not belong to you.');

                return;
            }
        }

        $maxOrder = $this->dashboard->widgets()->max('sort_order') ?? -1;

        Widget::query()->create([
            'dashboard_id' => $this->dashboard->id,
            'type' => $this->widgetType,
            'name' => $this->widgetName,
            'cloud_variable_id' => $this->cloudVariableId,
            'sort_order' => $maxOrder + 1,
        ]);

        $this->reset('widgetType', 'widgetName', 'cloudVariableId');
        $this->dashboard->load('widgets');

        Flux::modal('add-widget')->close();
        Flux::toast(text: 'Widget has been added.', heading: 'Widget added', variant: 'success');
    }

    public function deleteWidget(int $widgetId): void
    {
        Widget::query()
            ->where('id', $widgetId)
            ->where('dashboard_id', $this->dashboard->id)
            ->delete();

        $this->dashboard->load('widgets');

        Flux::toast(text: 'Widget has been removed.', heading: 'Widget deleted', variant: 'success');
    }

    public function deleteDashboard(): void
    {
        $this->dashboard->delete();

        Flux::toast(text: 'The dashboard has been removed.', heading: 'Dashboard deleted', variant: 'success');

        $this->redirect(route('dashboards.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.dashboards.edit');
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
