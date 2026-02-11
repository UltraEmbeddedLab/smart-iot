<flux:card wire:key="widget-{{ $widget->id }}">
    <div class="flex items-center gap-2">
        <flux:icon name="bolt" class="size-4 text-zinc-400" />
        <flux:text class="text-sm font-medium">{{ $widget->name }}</flux:text>
    </div>

    <div class="mt-4 flex items-center justify-center py-4">
        @if ($variable)
            @php
                $isOn = (bool) ($variable->last_value['value'] ?? false);
            @endphp
            <button
                wire:click="toggleSwitch({{ $widget->id }})"
                class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors {{ $isOn ? 'bg-green-500' : 'bg-zinc-300 dark:bg-zinc-600' }}"
            >
                <span class="inline-block size-6 rounded-full bg-white shadow-sm transition-transform {{ $isOn ? 'translate-x-7' : 'translate-x-1' }}"></span>
            </button>
        @else
            <flux:text class="text-zinc-400">{{ __('No variable linked') }}</flux:text>
        @endif
    </div>

    @if ($variable)
        <flux:text class="text-center text-xs text-zinc-400">{{ $isOn ? __('ON') : __('OFF') }}</flux:text>
    @endif
</flux:card>
