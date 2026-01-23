<?php declare(strict_types=1);

namespace App\Enums;

enum DeviceType: string
{
    case Arduino = 'arduino';
    case Esp32 = 'esp32';
    case Esp8266 = 'esp8266';
    case Stm32 = 'stm32';
    case RaspberryPi = 'raspberry_pi';
    case Generic = 'generic';

    public function label(): string
    {
        return match ($this) {
            self::Arduino => 'Arduino',
            self::Esp32 => 'ESP32',
            self::Esp8266 => 'ESP8266',
            self::Stm32 => 'STM32',
            self::RaspberryPi => 'Raspberry Pi',
            self::Generic => 'Generic',
        };
    }
}
