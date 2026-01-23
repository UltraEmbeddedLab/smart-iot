<?php declare(strict_types=1);

namespace App\Enums;

enum DeviceStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Provisioning = 'provisioning';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
            self::Provisioning => 'Provisioning',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Online => 'green',
            self::Offline => 'red',
            self::Provisioning => 'yellow',
        };
    }
}
