<x-layouts::app :title="__('Edit Device')">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('devices.show', $device)" wire:navigate />
            <div>
                <flux:heading size="xl">{{ __('Edit Device') }}</flux:heading>
                <flux:text class="mt-1">{{ $device->name }}</flux:text>
            </div>
        </div>

        <flux:card>
            <form wire:submit="updateDevice" class="space-y-4">
                <flux:input wire:model="name" :label="__('Device Name')" required autofocus />
                <flux:error name="name" />

                <div class="flex items-center gap-3">
                    <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
                    <flux:button variant="ghost" :href="route('devices.show', $device)" wire:navigate>{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>
