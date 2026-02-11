<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('dashboards.index')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Create Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Set up a new dashboard to visualize your IoT data.') }}</flux:text>
        </div>
    </div>

    <flux:card>
        <form wire:submit="createDashboard" class="space-y-4">
            <flux:input wire:model="name" :label="__('Name')" placeholder="{{ __('e.g. Living Room Monitor') }}" required autofocus autocomplete="off" />
            <flux:error name="name" />

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Create Dashboard') }}</flux:button>
                <flux:button variant="ghost" :href="route('dashboards.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
