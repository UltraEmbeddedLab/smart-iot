<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('things.show', $thing)" wire:navigate />
            <div>
                <flux:heading size="xl">{{ __('Generate Firmware') }}</flux:heading>
                <flux:text class="mt-1">{{ $thing->name }}</flux:text>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Configuration --}}
        <div class="space-y-6">
            <flux:card>
                <flux:heading size="lg">{{ __('Configuration') }}</flux:heading>

                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Device') }}</flux:text>
                        <flux:text>{{ $thing->device->name }}</flux:text>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Type') }}</flux:text>
                        <flux:badge size="sm">{{ $thing->device->type->label() }}</flux:badge>
                    </div>
                    <flux:separator />
                    <div class="flex justify-between">
                        <flux:text class="font-medium">{{ __('Variables') }}</flux:text>
                        <flux:text>{{ $thing->cloudVariables->count() }}</flux:text>
                    </div>
                </dl>

                @if ($thing->cloudVariables->isEmpty())
                    <div class="mt-4">
                        <flux:callout variant="warning" icon="information-circle">
                            {{ __('No cloud variables defined. A basic connectivity skeleton will be generated.') }}
                        </flux:callout>
                    </div>
                @endif

                <form wire:submit="generateCode" class="mt-6 space-y-4">
                    <flux:field>
                        <flux:label>{{ __('WiFi SSID') }}</flux:label>
                        <flux:input wire:model="wifiSsid" placeholder="{{ __('Your WiFi network name') }}" autocomplete="off" />
                        <flux:error name="wifiSsid" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('WiFi Password') }}</flux:label>
                        <flux:input wire:model="wifiPassword" type="password" placeholder="{{ __('Your WiFi password') }}" autocomplete="new-password" />
                        <flux:error name="wifiPassword" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full" icon="sparkles" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="generateCode">
                            {{ $generatedCode ? __('Regenerate Code') : __('Generate Code') }}
                        </span>
                        <span wire:loading wire:target="generateCode">
                            {{ __('Generating...') }}
                        </span>
                    </flux:button>
                </form>
            </flux:card>

            {{-- Saved Firmware --}}
            <flux:card>
                <flux:heading size="lg">{{ __('Saved Firmware') }}</flux:heading>

                @if ($thing->firmware->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($thing->firmware->sortByDesc('created_at') as $fw)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <div>
                                    <flux:text class="font-medium">{{ $fw->name }}</flux:text>
                                    <flux:text class="text-xs">{{ $fw->device_type->label() }} &middot; {{ $fw->created_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:button variant="ghost" size="sm" icon="code-bracket" wire:click="loadFirmware({{ $fw->id }})" />
                                    <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteFirmware({{ $fw->id }})" wire:confirm="{{ __('Delete this firmware?') }}" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="mt-4 text-sm">{{ __('No firmware saved yet. Generate code and save it.') }}</flux:text>
                @endif
            </flux:card>
        </div>

        {{-- Generated Code --}}
        <div class="lg:col-span-2">
            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Generated Code') }}</flux:heading>

                    @if ($generatedCode)
                        <div class="flex items-center gap-2">
                            <flux:button variant="ghost" size="sm" icon="clipboard" x-on:click="navigator.clipboard.writeText($wire.generatedCode); $flux.toast('{{ __('Copied to clipboard!') }}')">
                                {{ __('Copy') }}
                            </flux:button>
                            <flux:modal.trigger name="save-firmware">
                                <flux:button variant="primary" size="sm" icon="arrow-down-tray">
                                    {{ __('Save') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </div>
                    @endif
                </div>

                @if ($errorMessage)
                    <div class="mt-4">
                        <flux:callout variant="danger" icon="exclamation-triangle">
                            {{ $errorMessage }}
                        </flux:callout>
                    </div>
                @endif

                <div class="mt-4" wire:loading wire:target="generateCode">
                    <div class="space-y-3">
                        <flux:skeleton class="h-4 w-3/4" />
                        <flux:skeleton class="h-4 w-full" />
                        <flux:skeleton class="h-4 w-5/6" />
                        <flux:skeleton class="h-4 w-full" />
                        <flux:skeleton class="h-4 w-2/3" />
                        <flux:skeleton class="h-4 w-full" />
                        <flux:skeleton class="h-4 w-4/5" />
                        <flux:skeleton class="h-4 w-full" />
                    </div>
                </div>

                <div wire:loading.remove wire:target="generateCode">
                    @if ($generatedCode)
                        <pre class="mt-4 max-h-[70vh] overflow-auto rounded-lg bg-zinc-900 p-4 text-sm text-green-400"><code>{{ $generatedCode }}</code></pre>
                    @else
                        <div class="mt-4 flex flex-col items-center justify-center rounded-lg border border-dashed border-zinc-300 p-12 dark:border-zinc-700">
                            <flux:icon name="code-bracket" class="size-12 text-zinc-400" />
                            <flux:text class="mt-3">{{ __('Configure WiFi credentials and click Generate to create firmware code for your device.') }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>
    </div>

    {{-- Save Firmware Modal --}}
    <flux:modal name="save-firmware" class="min-w-[22rem]">
        <form wire:submit="saveFirmware" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Save Firmware') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Give this firmware a name so you can find it later.') }}</flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="firmwareName" placeholder="{{ __('e.g. v1.0 - Temperature Sensor') }}" autocomplete="off" />
                <flux:error name="firmwareName" />
            </flux:field>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
