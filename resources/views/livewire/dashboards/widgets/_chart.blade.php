<flux:card wire:key="widget-{{ $widget->id }}">
    <div class="flex items-center gap-2">
        <flux:icon name="chart-bar-square" class="size-4 text-zinc-400" />
        <flux:text class="text-sm font-medium">{{ $widget->name }}</flux:text>
    </div>

    <div class="mt-4 flex items-center justify-center py-4">
        @if ($variable && $variable->last_value !== null)
            <div class="text-center">
                <span class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $variable->last_value['value'] ?? '—' }}</span>
                @if ($variable->value_updated_at)
                    <div class="mt-1">
                        <flux:text class="text-xs text-zinc-400">{{ $variable->value_updated_at->format('M d, Y H:i:s') }}</flux:text>
                    </div>
                @endif
            </div>
        @else
            <flux:text class="text-zinc-400">{{ __('No data') }}</flux:text>
        @endif
    </div>

    <flux:text class="text-center text-xs text-zinc-400">{{ __('Historical charting coming soon') }}</flux:text>
</flux:card>
