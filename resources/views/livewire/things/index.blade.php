<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Things') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your Things — logical containers for cloud variables.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('things.create')" wire:navigate>
            {{ __('Create Thing') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search things...') }}"
                        icon="magnifying-glass" autocomplete="off"/>
        </div>
    </div>

    <flux:table :paginate="$this->things">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Device') }}</flux:table.column>
            <flux:table.column>{{ __('Timezone') }}</flux:table.column>
            <flux:table.column>{{ __('Tags') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('Created') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->things as $thing)
                <flux:table.row :key="$thing->id">
                    <flux:table.cell variant="strong">
                        <flux:link :href="route('things.show', $thing)" wire:navigate>{{ $thing->name }}</flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($thing->device)
                            <flux:badge size="sm" inset="top bottom">{{ $thing->device->name }}</flux:badge>
                        @else
                            <flux:text class="text-zinc-400">{{ __('None') }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-sm">{{ $thing->timezone }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom">{{ $thing->tags->count() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $thing->created_at->diffForHumans() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"/>
                            <flux:menu>
                                <flux:menu.item icon="eye" :href="route('things.show', $thing)"
                                                wire:navigate>{{ __('View') }}</flux:menu.item>
                                <flux:menu.item icon="pencil-square" :href="route('things.edit', $thing)"
                                                wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                <flux:menu.separator/>
                                <flux:menu.item icon="trash" variant="danger" x-on:click="$flux.modal('delete-thing-{{ $thing->id }}').show()">{{ __('Delete') }}</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>

                        <flux:modal :name="'delete-thing-'.$thing->id" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('Delete Thing?') }}</flux:heading>
                                    <flux:text class="mt-2">{{ __('This will permanently delete :name and all its tags. This action cannot be undone.', ['name' => $thing->name]) }}</flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button variant="danger" wire:click="deleteThing({{ $thing->id }})">{{ __('Delete') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center">
                        <div class="py-8">
                            <flux:icon name="cube" class="mx-auto size-12 text-zinc-400"/>
                            <flux:heading size="sm" class="mt-4">{{ __('No things yet') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Get started by creating your first thing.') }}</flux:text>
                            <flux:button variant="primary" size="sm" icon="plus" :href="route('things.create')"
                                         wire:navigate class="mt-4">
                                {{ __('Create Thing') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
