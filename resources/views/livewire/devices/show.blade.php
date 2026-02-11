<div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" icon="arrow-left" :href="route('devices.index')" wire:navigate />
                <div>
                    <flux:heading size="xl">{{ $device->name }}</flux:heading>
                    <div class="mt-1 flex items-center gap-2">
                        <flux:badge size="sm" :color="$device->status->color()">{{ $device->status->label() }}</flux:badge>
                        <flux:badge size="sm">{{ $device->type->label() }}</flux:badge>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="ghost" icon="pencil-square" :href="route('devices.edit', $device)" wire:navigate>{{ __('Edit') }}</flux:button>
                <flux:button variant="danger" icon="trash" wire:click="deleteDevice" wire:confirm="{{ __('Are you sure you want to delete this device? This action cannot be undone.') }}">{{ __('Delete') }}</flux:button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Device Info --}}
            <flux:card>
                <flux:heading size="lg">{{ __('Device Information') }}</flux:heading>

                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Device ID') }}</flux:text>
                        <div class="flex items-center gap-1">
                            <code class="text-sm">{{ $device->device_id }}</code>
                            <flux:button variant="ghost" size="sm" icon="clipboard" x-on:click="navigator.clipboard.writeText('{{ $device->device_id }}'); $flux.toast('{{ __('Copied!') }}')" />
                        </div>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Type') }}</flux:text>
                        <flux:text>{{ $device->type->label() }}</flux:text>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Status') }}</flux:text>
                        <flux:badge size="sm" :color="$device->status->color()">{{ $device->status->label() }}</flux:badge>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Last Activity') }}</flux:text>
                        <flux:text>{{ $device->last_activity_at?->diffForHumans() ?? __('Never') }}</flux:text>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Created') }}</flux:text>
                        <flux:text>{{ $device->created_at->format('M d, Y H:i') }}</flux:text>
                    </div>
                </dl>
            </flux:card>

            {{-- Security --}}
            <flux:card>
                <flux:heading size="lg">{{ __('Security') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Manage the device\'s secret key for MQTT authentication.') }}</flux:text>

                <div class="mt-4" x-data="{ newKey: null }" x-on:secret-key-regenerated.window="newKey = $event.detail.key">
                    <template x-if="newKey">
                        <div class="space-y-3">
                            <flux:callout variant="warning" icon="exclamation-triangle">
                                <flux:callout.heading>{{ __('Save this key now!') }}</flux:callout.heading>
                                <flux:callout.text>{{ __('It will not be shown again.') }}</flux:callout.text>
                            </flux:callout>
                            <div class="flex items-center gap-2">
                                <flux:input readonly x-bind:value="newKey" class="font-mono" />
                                <flux:button variant="ghost" icon="clipboard" size="sm" x-on:click="navigator.clipboard.writeText(newKey); $flux.toast('{{ __('Copied!') }}')" />
                            </div>
                        </div>
                    </template>

                    <template x-if="!newKey">
                        <div>
                            <flux:text class="text-sm">{{ __('The secret key is encrypted and cannot be viewed. You can regenerate it if needed.') }}</flux:text>

                            <flux:modal.trigger name="regenerate-secret-key">
                                <flux:button variant="ghost" icon="arrow-path" class="mt-3">
                                    {{ __('Regenerate Secret Key') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </div>
                    </template>
                </div>
            </flux:card>

            <flux:modal name="regenerate-secret-key" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Regenerate Secret Key?') }}</flux:heading>
                        <flux:text class="mt-2">{{ __('The current secret key will be permanently invalidated. Any device using it will lose access.') }}</flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button variant="danger" wire:click="regenerateSecretKey">{{ __('Regenerate') }}</flux:button>
                    </div>
                </div>
            </flux:modal>
        </div>

        {{-- Metadata --}}
        @if ($device->metadata)
            <flux:card>
                <flux:heading size="lg">{{ __('Metadata') }}</flux:heading>
                <div class="mt-4">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Key') }}</flux:table.column>
                            <flux:table.column>{{ __('Value') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($device->metadata as $key => $value)
                                <flux:table.row :key="$key">
                                    <flux:table.cell variant="strong">{{ $key }}</flux:table.cell>
                                    <flux:table.cell class="font-mono text-sm">{{ is_array($value) ? json_encode($value) : $value }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        @endif
</div>
