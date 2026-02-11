<x-mail::message>
# Trigger Alert: {{ $triggerName }}

A trigger condition has been met on your IoT platform.

**Variable:** {{ $variableName }} ({{ $thingName }})

**Current Value:** {{ $currentValue }}

**Condition:** {{ $condition }}

**Triggered At:** {{ $triggeredAt }}

<x-mail::button :url="route('triggers.index')">
View Triggers
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
