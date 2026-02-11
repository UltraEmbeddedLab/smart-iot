<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Devices') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your IoT devices and monitor their status.') }}</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" :href="route('devices.create')" wire:navigate>
            {{ __('Add Device') }}
        </flux:button>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search devices...') }}"
                        icon="magnifying-glass"/>
        </div>
        <flux:select wire:model.live="filterType" placeholder="{{ __('All Types') }}" class="sm:max-w-48">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            @foreach ($deviceTypes as $type)
                <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterStatus" placeholder="{{ __('All Statuses') }}" class="sm:max-w-48">
            <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
            @foreach ($deviceStatuses as $status)
                <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table :paginate="$this->devices">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                               wire:click="sort('status')">{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Device ID') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'last_activity_at'" :direction="$sortDirection"
                               wire:click="sort('last_activity_at')">{{ __('Last Activity') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">{{ __('Created') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->devices as $device)
                <flux:table.row :key="$device->id">
                    <flux:table.cell variant="strong">
                        <flux:link :href="route('devices.show', $device)" wire:navigate>{{ $device->name }}</flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" inset="top bottom">{{ $device->type->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$device->status->color()"
                                    inset="top bottom">{{ $device->status->label() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell
                        class="font-mono text-xs">{{ Str::limit($device->device_id, 18) }}</flux:table.cell>
                    <flux:table.cell
                        class="whitespace-nowrap">{{ $device->last_activity_at?->diffForHumans() ?? '—' }}</flux:table.cell>
                    <flux:table.cell
                        class="whitespace-nowrap">{{ $device->created_at->diffForHumans() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"/>
                            <flux:menu>
                                <flux:menu.item icon="eye" :href="route('devices.show', $device)"
                                                wire:navigate>{{ __('View') }}</flux:menu.item>
                                <flux:menu.item icon="pencil-square" :href="route('devices.edit', $device)"
                                                wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                <flux:menu.separator/>
                                <flux:menu.item icon="trash" variant="danger" x-on:click="$flux.modal('delete-device-{{ $device->id }}').show()">{{ __('Delete') }}</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>

                        <flux:modal :name="'delete-device-'.$device->id" class="min-w-[22rem]">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('Delete Device?') }}</flux:heading>
                                    <flux:text class="mt-2">{{ __('This will permanently delete :name and all its data. This action cannot be undone.', ['name' => $device->name]) }}</flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button variant="danger" wire:click="deleteDevice({{ $device->id }})">{{ __('Delete') }}</flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center">
                        <div class="py-8">
                            <flux:icon name="cpu-chip" class="mx-auto size-12 text-zinc-400"/>
                            <flux:heading size="sm" class="mt-4">{{ __('No devices yet') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Get started by adding your first device.') }}</flux:text>
                            <flux:button variant="primary" size="sm" icon="plus" :href="route('devices.create')"
                                         wire:navigate class="mt-4">
                                {{ __('Add Device') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
