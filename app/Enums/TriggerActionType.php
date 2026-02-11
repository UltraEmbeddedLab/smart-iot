<?php declare(strict_types=1);

namespace App\Enums;

enum TriggerActionType: string
{
    case Email = 'email';
    case Webhook = 'webhook';
    case PushNotification = 'push_notification';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Webhook => 'Webhook',
            self::PushNotification => 'Push Notification',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Email => 'envelope',
            self::Webhook => 'globe-alt',
            self::PushNotification => 'bell',
        };
    }

    public function isImplemented(): bool
    {
        return match ($this) {
            self::Email, self::Webhook => true,
            self::PushNotification => false,
        };
    }
}
