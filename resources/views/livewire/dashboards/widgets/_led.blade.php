<flux:card wire:key="widget-{{ $widget->id }}">
    <div class="flex items-center gap-2">
        <flux:icon name="sun" class="size-4 text-zinc-400" />
        <flux:text class="text-sm font-medium">{{ $widget->name }}</flux:text>
    </div>

    <div class="mt-4 flex items-center justify-center py-4">
        @if ($variable)
            @php
                $isActive = (bool) ($variable->last_value['value'] ?? false);
            @endphp
            <div class="size-12 rounded-full {{ $isActive ? 'bg-green-500 shadow-lg shadow-green-500/50' : 'bg-zinc-300 dark:bg-zinc-600' }}"></div>
        @else
            <div class="size-12 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
        @endif
    </div>

    @if ($variable)
        <flux:text class="text-center text-xs text-zinc-400">{{ $isActive ? __('Active') : __('Inactive') }}</flux:text>
    @endif
</flux:card>
