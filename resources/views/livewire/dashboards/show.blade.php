<div wire:poll.2s>
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('dashboards.index')" wire:navigate />
            <div>
                <flux:heading size="xl">{{ $dashboard->name }}</flux:heading>
                <flux:text class="mt-1">{{ __(':count widget(s)', ['count' => $dashboard->widgets->count()]) }}</flux:text>
            </div>
        </div>
        <flux:button variant="ghost" icon="pencil-square" :href="route('dashboards.edit', $dashboard)" wire:navigate>
            {{ __('Edit') }}
        </flux:button>
    </div>

    @if ($dashboard->widgets->isEmpty())
        <flux:card>
            <div class="py-8 text-center">
                <flux:icon name="squares-2x2" class="mx-auto size-12 text-zinc-400"/>
                <flux:heading size="sm" class="mt-4">{{ __('No widgets yet') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Add widgets to this dashboard to start visualizing your data.') }}</flux:text>
                <flux:button variant="primary" size="sm" icon="plus" :href="route('dashboards.edit', $dashboard)" wire:navigate class="mt-4">
                    {{ __('Add Widgets') }}
                </flux:button>
            </div>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($dashboard->widgets as $widget)
                @php
                    $variable = $widget->cloudVariable;
                @endphp

                @if ($widget->type->isImplemented())
                    @include('livewire.dashboards.widgets._' . $widget->type->value, [
                        'widget' => $widget,
                        'variable' => $variable,
                    ])
                @else
                    <flux:card wire:key="widget-{{ $widget->id }}">
                        <div class="py-4 text-center">
                            <flux:icon :name="$widget->type->icon()" class="mx-auto size-8 text-zinc-400"/>
                            <flux:text class="mt-2 text-sm">{{ $widget->type->label() }} — {{ __('Coming soon') }}</flux:text>
                        </div>
                    </flux:card>
                @endif
            @endforeach
        </div>
    @endif
</div>
