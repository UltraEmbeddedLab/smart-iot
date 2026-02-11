<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('dashboards.show', $dashboard)" wire:navigate />
            <div>
                <flux:heading size="xl">{{ __('Edit Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ $dashboard->name }}</flux:text>
            </div>
        </div>
        <flux:modal.trigger name="delete-dashboard">
            <flux:button variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="delete-dashboard" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Dashboard?') }}</flux:heading>
                <flux:text class="mt-2">{{ __('This will permanently delete :name and all its widgets. This action cannot be undone.', ['name' => $dashboard->name]) }}</flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteDashboard">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Name Form --}}
    <flux:card>
        <flux:heading size="lg">{{ __('Dashboard Settings') }}</flux:heading>

        <form wire:submit="updateDashboard" class="mt-4 space-y-4">
            <flux:input wire:model="name" :label="__('Name')" required autocomplete="off" />
            <flux:error name="name" />

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
                <flux:button variant="ghost" :href="route('dashboards.show', $dashboard)" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>

    {{-- Widgets --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">{{ __('Widgets') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Manage the widgets displayed on this dashboard.') }}</flux:text>
            </div>
            <flux:modal.trigger name="add-widget">
                <flux:button variant="primary" size="sm" icon="plus">{{ __('Add Widget') }}</flux:button>
            </flux:modal.trigger>
        </div>

        @if ($dashboard->widgets->isNotEmpty())
            <div class="mt-4">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column>{{ __('Variable') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($dashboard->widgets as $widget)
                            <flux:table.row :key="$widget->id">
                                <flux:table.cell>
                                    <flux:badge size="sm" icon="{{ $widget->type->icon() }}">{{ $widget->type->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell variant="strong">{{ $widget->name }}</flux:table.cell>
                                <flux:table.cell>
                                    @if ($widget->cloudVariable)
                                        <flux:text class="text-sm">{{ $widget->cloudVariable->name }} ({{ $widget->cloudVariable->thing->name }})</flux:text>
                                    @else
                                        <flux:text class="text-sm text-zinc-400">{{ __('None') }}</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteWidget({{ $widget->id }})" wire:confirm="{{ __('Delete this widget?') }}" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <div class="mt-4">
                <flux:text class="text-sm">{{ __('No widgets have been added yet.') }}</flux:text>
            </div>
        @endif
    </flux:card>

    {{-- Add Widget Modal --}}
    <flux:modal name="add-widget" class="min-w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Add Widget') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Choose a widget type and link it to a cloud variable.') }}</flux:text>
            </div>

            <form wire:submit="addWidget" class="space-y-4">
                <flux:select wire:model="widgetType" :label="__('Widget Type')" placeholder="{{ __('Select type...') }}">
                    @foreach (\App\Enums\WidgetType::mvpCases() as $type)
                        <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="widgetType" />

                <flux:input wire:model="widgetName" :label="__('Name')" placeholder="{{ __('e.g. Temperature Reading') }}" autocomplete="off" />
                <flux:error name="widgetName" />

                <flux:select wire:model="cloudVariableId" :label="__('Cloud Variable')" placeholder="{{ __('Select variable...') }}">
                    <flux:select.option :value="null">{{ __('No variable') }}</flux:select.option>
                    @foreach ($this->availableVariables as $variable)
                        <flux:select.option :value="$variable->id">{{ $variable->name }} ({{ $variable->thing->name }})</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="cloudVariableId" />

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" type="submit">{{ __('Add Widget') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
