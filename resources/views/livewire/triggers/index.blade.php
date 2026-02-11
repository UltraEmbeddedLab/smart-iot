<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Triggers') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Automate actions based on cloud variable conditions.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('triggers.create')" wire:navigate>
            {{ __('Create Trigger') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search triggers...') }}"
                        icon="magnifying-glass" autocomplete="off"/>
        </div>
    </div>

    <flux:table :paginate="$this->triggers">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Variable') }}</flux:table.column>
            <flux:table.column>{{ __('Condition') }}</flux:table.column>
            <flux:table.column>{{ __('Action') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'last_triggered_at'" :direction="$sortDirection"
                               wire:click="sort('last_triggered_at')">{{ __('Last Triggered') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->triggers as $trigger)
                <flux:table.row :key="$trigger->id">
                    <flux:table.cell variant="strong">{{ $trigger->name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($trigger->cloudVariable)
                            <flux:text class="text-sm">{{ $trigger->cloudVariable->name }} ({{ $trigger->cloudVariable->thing->name }})</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom">{{ $trigger->operator->symbol() }} {{ $trigger->value }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" icon="{{ $trigger->action_type->icon() }}" inset="top bottom">{{ $trigger->action_type->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:switch wire:click="toggleActive({{ $trigger->id }})" :checked="$trigger->is_active" />
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $trigger->last_triggered_at?->diffForHumans() ?? __('Never') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"/>
                            <flux:menu>
                                <flux:menu.item icon="pencil-square" :href="route('triggers.edit', $trigger)"
                                                wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                <flux:menu.separator/>
                                <flux:menu.item icon="trash" variant="danger" x-on:click="$flux.modal('delete-trigger-{{ $trigger->id }}').show()">{{ __('Delete') }}</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>

                        <flux:modal :name="'delete-trigger-'.$trigger->id" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('Delete Trigger?') }}</flux:heading>
                                    <flux:text class="mt-2">{{ __('This will permanently delete :name. This action cannot be undone.', ['name' => $trigger->name]) }}</flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button variant="danger" wire:click="deleteTrigger({{ $trigger->id }})">{{ __('Delete') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center">
                        <div class="py-8">
                            <flux:icon name="bolt" class="mx-auto size-12 text-zinc-400"/>
                            <flux:heading size="sm" class="mt-4">{{ __('No triggers yet') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Get started by creating your first trigger.') }}</flux:text>
                            <flux:button variant="primary" size="sm" icon="plus" :href="route('triggers.create')"
                                         wire:navigate class="mt-4">
                                {{ __('Create Trigger') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
