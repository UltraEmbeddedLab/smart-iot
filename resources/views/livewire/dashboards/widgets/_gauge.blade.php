<flux:card wire:key="widget-{{ $widget->id }}">
    <div class="flex items-center gap-2">
        <flux:icon name="chart-bar" class="size-4 text-zinc-400" />
        <flux:text class="text-sm font-medium">{{ $widget->name }}</flux:text>
    </div>

    <div class="mt-4 flex items-center justify-center py-2">
        @if ($variable && $variable->last_value !== null)
            @php
                $currentValue = (float) ($variable->last_value['value'] ?? 0);
                $minValue = (float) ($variable->min_value ?? 0);
                $maxValue = (float) ($variable->max_value ?? 100);
                $range = $maxValue - $minValue;
                $percentage = $range > 0 ? (($currentValue - $minValue) / $range) * 100 : 0;
                $percentage = max(0, min(100, $percentage));
                $angle = ($percentage / 100) * 180;
            @endphp
            <div x-data="{ percentage: {{ $percentage }}, value: {{ $currentValue }} }" class="relative">
                <svg viewBox="0 0 200 120" class="w-40">
                    {{-- Background arc --}}
                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="currentColor" stroke-width="12" class="text-zinc-200 dark:text-zinc-700" stroke-linecap="round" />
                    {{-- Value arc --}}
                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="currentColor" stroke-width="12" class="text-blue-500" stroke-linecap="round"
                          :style="'stroke-dasharray: ' + (percentage * 2.51) + ' 251'" />
                </svg>
                <div class="absolute inset-x-0 bottom-0 text-center">
                    <span class="text-2xl font-bold text-zinc-900 dark:text-white" x-text="value"></span>
                </div>
            </div>
        @else
            <flux:text class="text-zinc-400">{{ __('No data') }}</flux:text>
        @endif
    </div>

    @if ($variable && $variable->value_updated_at)
        <flux:text class="text-center text-xs text-zinc-400">{{ $variable->value_updated_at->diffForHumans() }}</flux:text>
    @endif
</flux:card>
