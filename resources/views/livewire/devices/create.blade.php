<div class="mx-auto max-w-2xl space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Add Device') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Configure a new device for your IoT Cloud.') }}</flux:text>
        </div>

        {{-- Step indicator --}}
        <div class="flex items-center gap-2">
            @foreach ([1 => 'Type', 2 => 'Name', 3 => 'Credentials'] as $num => $label)
                <div class="flex items-center gap-2">
                    <div @class([
                        'flex size-8 items-center justify-center rounded-full text-sm font-medium',
                        'bg-zinc-800 text-white dark:bg-white dark:text-zinc-900' => $step >= $num,
                        'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $step < $num,
                    ])>{{ $num }}</div>
                    <flux:text class="text-sm">{{ __($label) }}</flux:text>
                </div>
                @if ($num < 3)
                    <div @class([
                        'h-px flex-1',
                        'bg-zinc-800 dark:bg-white' => $step > $num,
                        'bg-zinc-200 dark:bg-zinc-700' => $step <= $num,
                    ])></div>
                @endif
            @endforeach
        </div>

        {{-- Step 1: Choose device type --}}
        @if ($step === 1)
            <flux:card>
                <flux:heading size="lg">{{ __('Choose Device Type') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Select the type of board you want to configure.') }}</flux:text>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach (\App\Enums\DeviceType::cases() as $deviceType)
                        <button
                            wire:click="selectType('{{ $deviceType->value }}')"
                            class="flex flex-col items-center gap-2 rounded-lg border border-zinc-200 p-4 transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-500 dark:hover:bg-zinc-800"
                        >
                            <flux:icon name="cpu-chip" class="size-8 text-zinc-600 dark:text-zinc-400" />
                            <flux:text class="text-sm font-medium">{{ $deviceType->label() }}</flux:text>
                        </button>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- Step 2: Enter device name --}}
        @if ($step === 2)
            <flux:card>
                <flux:heading size="lg">{{ __('Name Your Device') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Give your :type device a recognizable name.', ['type' => \App\Enums\DeviceType::from($type)->label()]) }}</flux:text>

                <form wire:submit="createDevice" class="mt-6 space-y-4">
                    <flux:input wire:model="name" :label="__('Device Name')" placeholder="{{ __('e.g. Living Room Sensor') }}" required autofocus />
                    <flux:error name="name" />

                    <div class="flex items-center gap-3">
                        <flux:button variant="ghost" wire:click="goBack">{{ __('Back') }}</flux:button>
                        <flux:button variant="primary" type="submit">{{ __('Create Device') }}</flux:button>
                    </div>
                </form>
            </flux:card>
        @endif

        {{-- Step 3: Show credentials --}}
        @if ($step === 3)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('Save your credentials now!') }}</flux:callout.heading>
                <flux:callout.text>{{ __('The Secret Key will only be shown once. If you lose it, you will need to regenerate it.') }}</flux:callout.text>
            </flux:callout>

            <flux:card>
                <flux:heading size="lg">{{ __('Device Created Successfully') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Use these credentials on your microcontroller to connect to the cloud.') }}</flux:text>

                <div class="mt-6 space-y-4">
                    <div>
                        <flux:label>{{ __('Device ID') }}</flux:label>
                        <div class="mt-1 flex items-center gap-2">
                            <flux:input readonly :value="$deviceId" class="font-mono" />
                            <flux:button variant="ghost" icon="clipboard" size="sm" x-on:click="navigator.clipboard.writeText('{{ $deviceId }}'); $flux.toast('{{ __('Copied!') }}')" />
                        </div>
                    </div>

                    <div>
                        <flux:label>{{ __('Secret Key') }}</flux:label>
                        <div class="mt-1 flex items-center gap-2">
                            <flux:input readonly :value="$secretKey" class="font-mono" />
                            <flux:button variant="ghost" icon="clipboard" size="sm" x-on:click="navigator.clipboard.writeText('{{ $secretKey }}'); $flux.toast('{{ __('Copied!') }}')" />
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <flux:button variant="primary" :href="route('devices.index')" wire:navigate>{{ __('Go to Devices') }}</flux:button>
                </div>
            </flux:card>
        @endif
</div>
