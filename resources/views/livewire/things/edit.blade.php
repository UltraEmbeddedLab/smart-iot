<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('things.show', $thing)" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Edit Thing') }}</flux:heading>
            <flux:text class="mt-1">{{ $thing->name }}</flux:text>
        </div>
    </div>

    <flux:card>
        <form wire:submit="updateThing" class="space-y-4">
            <flux:input wire:model="name" :label="__('Name')" required autofocus autocomplete="off" />
            <flux:error name="name" />

            <flux:select variant="combobox" wire:model="timezone" :label="__('Timezone')" placeholder="{{ __('Select timezone...') }}">
                @foreach ($timezones as $tz)
                    <flux:select.option :value="$tz">{{ $tz }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="timezone" />

            <flux:select variant="combobox" wire:model="device_id" :label="__('Device')" placeholder="{{ __('No device') }}">
                <flux:select.option :value="null">{{ __('No device') }}</flux:select.option>
                @foreach ($this->availableDevices as $device)
                    <flux:select.option :value="$device->id">{{ $device->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="device_id" />

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
                <flux:button variant="ghost" :href="route('things.show', $thing)" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
