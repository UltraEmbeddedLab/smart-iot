<flux:card wire:key="widget-{{ $widget->id }}">
    <div class="flex items-center gap-2">
        <flux:icon name="adjustments-horizontal" class="size-4 text-zinc-400" />
        <flux:text class="text-sm font-medium">{{ $widget->name }}</flux:text>
    </div>

    <div class="mt-4 space-y-2 py-2">
        @if ($variable)
            @php
                $currentValue = (float) ($variable->last_value['value'] ?? 0);
                $minValue = (float) ($variable->min_value ?? 0);
                $maxValue = (float) ($variable->max_value ?? 100);
            @endphp
            <div class="flex items-center justify-between text-xs">
                <flux:text>{{ $minValue }}</flux:text>
                <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ $currentValue }}</span>
                <flux:text>{{ $maxValue }}</flux:text>
            </div>
            <input
                type="range"
                min="{{ $minValue }}"
                max="{{ $maxValue }}"
                step="1"
                value="{{ $currentValue }}"
                x-on:change="$wire.updateSlider({{ $widget->id }}, parseFloat($event.target.value))"
                class="w-full accent-blue-500"
            />
        @else
            <flux:text class="text-center text-zinc-400">{{ __('No variable linked') }}</flux:text>
        @endif
    </div>
</flux:card>
