<flux:card wire:key="widget-{{ $widget->id }}">
    <div class="flex items-center gap-2">
        <flux:icon name="hashtag" class="size-4 text-zinc-400" />
        <flux:text class="text-sm font-medium">{{ $widget->name }}</flux:text>
    </div>

    <div class="mt-4 flex items-center justify-center py-4">
        @if ($variable && $variable->last_value !== null)
            <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ $variable->last_value['value'] ?? '—' }}</span>
        @else
            <flux:text class="text-zinc-400">{{ __('No data') }}</flux:text>
        @endif
    </div>

    @if ($variable && $variable->value_updated_at)
        <flux:text class="text-center text-xs text-zinc-400">{{ $variable->value_updated_at->diffForHumans() }}</flux:text>
    @endif
</flux:card>
