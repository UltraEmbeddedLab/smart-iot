<?php declare(strict_types=1);

namespace App\Livewire\Dashboards;

use App\Enums\VariablePermission;
use App\Enums\WidgetType;
use App\Models\CloudVariable;
use App\Models\Dashboard;
use App\Models\Widget;
use App\Services\MqttService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
final class Show extends Component
{
    public Dashboard $dashboard;

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->user_id === Auth::id(), 403);

        $this->dashboard = $dashboard;
    }

    public function toggleSwitch(int $widgetId): void
    {
        $widget = $this->resolveWritableWidget($widgetId, WidgetType::Switch);

        if (! $widget) {
            return;
        }

        /** @var CloudVariable $variable */
        $variable = $widget->cloudVariable;
        $currentValue = $variable->last_value['value'] ?? false;
        $newValue = ! $currentValue;

        $variable->updateValue($newValue);

        /** @var \App\Models\Thing $thing */
        $thing = $variable->thing;

        app(MqttService::class)->publishToThing(
            $thing->uuid,
            [$variable->variable_name => $newValue],
        );
    }

    public function updateSlider(int $widgetId, float $value): void
    {
        $widget = $this->resolveWritableWidget($widgetId, WidgetType::Slider);

        if (! $widget) {
            return;
        }

        /** @var CloudVariable $variable */
        $variable = $widget->cloudVariable;

        if ($variable->min_value !== null && $value < (float) $variable->min_value) {
            $value = (float) $variable->min_value;
        }

        if ($variable->max_value !== null && $value > (float) $variable->max_value) {
            $value = (float) $variable->max_value;
        }

        $variable->updateValue($value);

        /** @var \App\Models\Thing $thing */
        $thing = $variable->thing;

        app(MqttService::class)->publishToThing(
            $thing->uuid,
            [$variable->variable_name => $value],
        );
    }

    public function render(): View
    {
        $this->dashboard->load(['widgets.cloudVariable.thing']);

        return view('livewire.dashboards.show');
    }

    private function resolveWritableWidget(int $widgetId, WidgetType $expectedType): ?Widget
    {
        $widget = Widget::query()
            ->where('id', $widgetId)
            ->where('dashboard_id', $this->dashboard->id)
            ->with('cloudVariable.thing')
            ->first();

        if (! $widget || $widget->type !== $expectedType) {
            return null;
        }

        /** @var CloudVariable|null $cloudVariable */
        $cloudVariable = $widget->cloudVariable;

        if (! $cloudVariable) {
            return null;
        }

        if ($cloudVariable->permission !== VariablePermission::ReadWrite) {
            return null;
        }

        return $widget;
    }
}
