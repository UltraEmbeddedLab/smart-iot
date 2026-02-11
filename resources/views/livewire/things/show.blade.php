<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('things.index')" wire:navigate />
            <div>
                <flux:heading size="xl">{{ $thing->name }}</flux:heading>
                <flux:text class="mt-1 font-mono text-xs">{{ $thing->uuid }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" icon="pencil-square" :href="route('things.edit', $thing)" wire:navigate>{{ __('Edit') }}</flux:button>
            <flux:modal.trigger name="delete-thing">
                <flux:button variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <flux:modal name="delete-thing" class="min-w-[22rem]">
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
                <flux:button variant="danger" wire:click="deleteThing">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Thing Info --}}
        <flux:card>
            <flux:heading size="lg">{{ __('Thing Information') }}</flux:heading>

            <dl class="mt-4 space-y-3">
                <div class="flex justify-between">
                    <flux:text class="font-medium">{{ __('UUID') }}</flux:text>
                    <div class="flex items-center gap-1">
                        <code class="text-sm">{{ $thing->uuid }}</code>
                        <flux:button variant="ghost" size="sm" icon="clipboard" x-on:click="navigator.clipboard.writeText('{{ $thing->uuid }}'); $flux.toast('{{ __('Copied!') }}')" />
                    </div>
                </div>
                <flux:separator />
                <div class="flex justify-between">
                    <flux:text class="font-medium">{{ __('Timezone') }}</flux:text>
                    <flux:text>{{ $thing->timezone }}</flux:text>
                </div>
                <flux:separator />
                <div class="flex justify-between">
                    <flux:text class="font-medium">{{ __('Created') }}</flux:text>
                    <flux:text>{{ $thing->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
            </dl>
        </flux:card>

        {{-- Device --}}
        <flux:card>
            <flux:heading size="lg">{{ __('Device') }}</flux:heading>

            @if ($thing->device)
                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Name') }}</flux:text>
                        <flux:link :href="route('devices.show', $thing->device)" wire:navigate>{{ $thing->device->name }}</flux:link>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Status') }}</flux:text>
                        <flux:badge size="sm" :color="$thing->device->status->color()">{{ $thing->device->status->label() }}</flux:badge>
                    </div>
                </dl>

                <div class="mt-4">
                    <flux:modal.trigger name="detach-device">
                        <flux:button variant="ghost" size="sm" icon="link-slash">{{ __('Detach Device') }}</flux:button>
                    </flux:modal.trigger>
                </div>

                <flux:modal name="detach-device" class="min-w-[22rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('Detach Device?') }}</flux:heading>
                            <flux:text class="mt-2">{{ __('This will remove the association between :thing and :device. No data will be lost.', ['thing' => $thing->name, 'device' => $thing->device->name]) }}</flux:text>
                        </div>

                        <div class="flex gap-2">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button variant="danger" wire:click="detachDevice">{{ __('Detach') }}</flux:button>
                        </div>
                    </div>
                </flux:modal>
            @else
                <div class="mt-4">
                    <flux:text class="text-sm">{{ __('No device is associated with this thing.') }}</flux:text>
                    <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('things.edit', $thing)" wire:navigate class="mt-2">
                        {{ __('Assign a Device') }}
                    </flux:button>
                </div>
            @endif
        </flux:card>
    </div>

    {{-- Tags --}}
    <flux:card>
        <flux:heading size="lg">{{ __('Tags') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Key-value metadata for your thing.') }}</flux:text>

        <div class="mt-4">
            <form wire:submit="addTag" class="flex items-end gap-3">
                <div class="flex-1">
                    <flux:input wire:model="tagKey" :label="__('Key')" placeholder="{{ __('e.g. location') }}" size="sm" autocomplete="off" />
                </div>
                <div class="flex-1">
                    <flux:input wire:model="tagValue" :label="__('Value')" placeholder="{{ __('e.g. office') }}" size="sm" autocomplete="off" />
                </div>
                <flux:button type="submit" variant="primary" size="sm" icon="plus">{{ __('Add') }}</flux:button>
            </form>
            <flux:error name="tagKey" />
            <flux:error name="tagValue" />
        </div>

        @if ($thing->tags->isNotEmpty())
            <div class="mt-4">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Key') }}</flux:table.column>
                        <flux:table.column>{{ __('Value') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($thing->tags as $tag)
                            <flux:table.row :key="$tag->id">
                                <flux:table.cell variant="strong">{{ $tag->key }}</flux:table.cell>
                                <flux:table.cell>{{ $tag->value }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteTag({{ $tag->id }})" wire:confirm="{{ __('Delete this tag?') }}" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif
    </flux:card>

    {{-- Cloud Variables --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">{{ __('Cloud Variables') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Typed variables that represent IoT data for this thing.') }}</flux:text>
            </div>
            <flux:button variant="primary" size="sm" icon="plus" :href="route('things.variables.create', $thing)" wire:navigate>
                {{ __('Add Variable') }}
            </flux:button>
        </div>

        @if ($thing->cloudVariables->isNotEmpty())
            <div class="mt-4">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Declaration') }}</flux:table.column>
                        <flux:table.column>{{ __('Permission') }}</flux:table.column>
                        <flux:table.column>{{ __('Last Value') }}</flux:table.column>
                        <flux:table.column>{{ __('Updated At') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($thing->cloudVariables as $variable)
                            <flux:table.row :key="$variable->id">
                                <flux:table.cell variant="strong">{{ $variable->name }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm">{{ $variable->type->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <code class="text-xs">{{ $variable->declaration }}</code>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$variable->permission->value === 'read_only' ? 'yellow' : 'green'">{{ $variable->permission->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($variable->last_value !== null)
                                        <code class="text-xs">{{ json_encode($variable->last_value) }}</code>
                                    @else
                                        <flux:text class="text-xs">&mdash;</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($variable->value_updated_at)
                                        <flux:text class="text-xs">{{ $variable->value_updated_at->format('M d, Y H:i') }}</flux:text>
                                    @else
                                        <flux:text class="text-xs">&mdash;</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button variant="ghost" size="sm" icon="pencil-square" :href="route('things.variables.edit', [$thing, $variable])" wire:navigate />
                                        <flux:modal.trigger name="delete-variable-{{ $variable->id }}">
                                            <flux:button variant="ghost" size="sm" icon="trash" />
                                        </flux:modal.trigger>
                                    </div>

                                    <flux:modal name="delete-variable-{{ $variable->id }}" class="min-w-[22rem]">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">{{ __('Delete Variable?') }}</flux:heading>
                                                <flux:text class="mt-2">{{ __('This will permanently delete the variable :name. This action cannot be undone.', ['name' => $variable->name]) }}</flux:text>
                                            </div>

                                            <div class="flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                                </flux:modal.close>
                                                <flux:button variant="danger" wire:click="deleteVariable({{ $variable->id }})">{{ __('Delete') }}</flux:button>
                                            </div>
                                        </div>
                                    </flux:modal>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <div class="mt-4">
                <flux:text class="text-sm">{{ __('No cloud variables have been added yet.') }}</flux:text>
            </div>
        @endif
    </flux:card>
</div>
