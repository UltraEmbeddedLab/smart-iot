<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('things.show', $thing)" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Edit Cloud Variable') }}</flux:heading>
            <flux:text class="mt-1">{{ $thing->name }} &mdash; {{ $variable->variable_name }}</flux:text>
        </div>
    </div>

    <flux:card>
        <form wire:submit="updateVariable" class="space-y-4">
            <flux:input wire:model="name" :label="__('Name')" required autofocus autocomplete="off" />
            <flux:error name="name" />

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-xs font-medium">{{ __('Type') }}</flux:text>
                <div class="mt-1">
                    <flux:badge size="sm">{{ $variable->type->label() }}</flux:badge>
                </div>

                <flux:text class="mt-2 text-xs font-medium">{{ __('C++ Declaration') }}</flux:text>
                <code class="mt-1 block text-sm">{{ $variable->declaration }}</code>
            </div>

            <flux:select wire:model="permission" :label="__('Permission')">
                @foreach ($permissions as $p)
                    <flux:select.option :value="$p->value">{{ $p->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="permission" />

            <flux:select wire:model.live="update_policy" :label="__('Update Policy')">
                @foreach ($policies as $policy)
                    <flux:select.option :value="$policy->value">{{ $policy->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="update_policy" />

            <flux:input wire:model="update_parameter" type="number" step="0.01" min="0"
                :label="$update_policy === 'periodically' ? __('Interval (seconds)') : __('Threshold')"
                :placeholder="$update_policy === 'periodically' ? __('e.g. 60') : __('e.g. 0.5')"
                autocomplete="off" />
            <flux:error name="update_parameter" />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="min_value" type="number" step="0.01" :label="__('Minimum Value')" placeholder="{{ __('e.g. -40') }}" autocomplete="off" />
                    <flux:error name="min_value" />
                </div>
                <div>
                    <flux:input wire:model="max_value" type="number" step="0.01" :label="__('Maximum Value')" placeholder="{{ __('e.g. 80') }}" autocomplete="off" />
                    <flux:error name="max_value" />
                </div>
            </div>

            <flux:field variant="inline">
                <flux:label>{{ __('Persist last value') }}</flux:label>
                <flux:switch wire:model="persist" />
            </flux:field>

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
                <flux:button variant="ghost" :href="route('things.show', $thing)" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
