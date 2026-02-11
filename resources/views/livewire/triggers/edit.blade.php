<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" :href="route('triggers.index')" wire:navigate />
        <div>
            <flux:heading size="xl">{{ __('Edit Trigger') }}</flux:heading>
            <flux:text class="mt-1">{{ $trigger->name }}</flux:text>
        </div>
    </div>

    <flux:card>
        <form wire:submit="updateTrigger" class="space-y-4">
            <flux:input wire:model="name" :label="__('Name')" required autofocus autocomplete="off" />
            <flux:error name="name" />

            <flux:select wire:model="cloudVariableId" :label="__('Cloud Variable')" placeholder="{{ __('Select variable...') }}">
                @foreach ($this->availableVariables as $variable)
                    <flux:select.option :value="$variable->id">{{ $variable->name }} ({{ $variable->thing->name }})</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="cloudVariableId" />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:select wire:model="operator" :label="__('Operator')">
                        <flux:select.option value="">{{ __('Select operator...') }}</flux:select.option>
                        @foreach (\App\Enums\TriggerOperator::cases() as $op)
                            <flux:select.option :value="$op->value">{{ $op->label() }} ({{ $op->symbol() }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="operator" />
                </div>
                <div>
                    <flux:input wire:model="value" :label="__('Value')" placeholder="{{ __('e.g. 100') }}" autocomplete="off" />
                    <flux:error name="value" />
                </div>
            </div>

            <flux:select wire:model.live="actionType" :label="__('Action Type')">
                <flux:select.option value="">{{ __('Select action...') }}</flux:select.option>
                @foreach (\App\Enums\TriggerActionType::cases() as $action)
                    @if ($action->isImplemented())
                        <flux:select.option :value="$action->value">{{ $action->label() }}</flux:select.option>
                    @endif
                @endforeach
            </flux:select>
            <flux:error name="actionType" />

            @if ($actionType === 'email')
                <flux:input wire:model="actionConfig.email" type="email" :label="__('Email Address')" placeholder="{{ __('alerts@example.com') }}" autocomplete="off" />
                <flux:error name="actionConfig.email" />
            @elseif ($actionType === 'webhook')
                <flux:input wire:model="actionConfig.url" type="url" :label="__('Webhook URL')" placeholder="{{ __('https://example.com/webhook') }}" autocomplete="off" />
                <flux:error name="actionConfig.url" />
            @endif

            <flux:input wire:model="cooldownSeconds" type="number" :label="__('Cooldown (seconds)')" min="0" placeholder="{{ __('0 = no cooldown') }}" />
            <flux:error name="cooldownSeconds" />

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
                <flux:button variant="ghost" :href="route('triggers.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
