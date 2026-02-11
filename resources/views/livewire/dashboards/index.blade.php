<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Dashboards') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Create and manage dashboards to visualize your IoT data.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('dashboards.create')" wire:navigate>
            {{ __('Create Dashboard') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search dashboards...') }}"
                        icon="magnifying-glass" autocomplete="off"/>
        </div>
    </div>

    <flux:table :paginate="$this->dashboards">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Widgets') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('Created') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->dashboards as $dashboard)
                <flux:table.row :key="$dashboard->id">
                    <flux:table.cell variant="strong">
                        <flux:link :href="route('dashboards.show', $dashboard)" wire:navigate>{{ $dashboard->name }}</flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom">{{ $dashboard->widgets_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $dashboard->created_at->diffForHumans() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"/>
                            <flux:menu>
                                <flux:menu.item icon="eye" :href="route('dashboards.show', $dashboard)"
                                                wire:navigate>{{ __('View') }}</flux:menu.item>
                                <flux:menu.item icon="pencil-square" :href="route('dashboards.edit', $dashboard)"
                                                wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                <flux:menu.separator/>
                                <flux:menu.item icon="trash" variant="danger" x-on:click="$flux.modal('delete-dashboard-{{ $dashboard->id }}').show()">{{ __('Delete') }}</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>

                        <flux:modal :name="'delete-dashboard-'.$dashboard->id" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('Delete Dashboard?') }}</flux:heading>
                                    <flux:text class="mt-2">{{ __('This will permanently delete :name and all its widgets. This action cannot be undone.', ['name' => $dashboard->name]) }}</flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button variant="danger" wire:click="deleteDashboard({{ $dashboard->id }})">{{ __('Delete') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center">
                        <div class="py-8">
                            <flux:icon name="squares-2x2" class="mx-auto size-12 text-zinc-400"/>
                            <flux:heading size="sm" class="mt-4">{{ __('No dashboards yet') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Get started by creating your first dashboard.') }}</flux:text>
                            <flux:button variant="primary" size="sm" icon="plus" :href="route('dashboards.create')"
                                         wire:navigate class="mt-4">
                                {{ __('Create Dashboard') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
