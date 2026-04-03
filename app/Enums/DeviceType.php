<?php declare(strict_types=1);

namespace App\Enums;

enum DeviceType: string
{
    case Arduino = 'arduino';
    case Esp32 = 'esp32';
    case Esp8266 = 'esp8266';
    case Stm32 = 'stm32';
    case Pico = 'pico';
    case RaspberryPi = 'raspberry_pi';
    case Generic = 'generic';

    public function label(): string
    {
        return match ($this) {
            self::Arduino => 'Arduino',
            self::Esp32 => 'ESP32',
            self::Esp8266 => 'ESP8266',
            self::Stm32 => 'STM32',
            self::Pico => 'Raspberry Pi Pico',
            self::RaspberryPi => 'Raspberry Pi',
            self::Generic => 'Generic',
        };
    }

    public function libraries(): string
    {
        return match ($this) {
            self::Arduino => 'WiFiNINA.h, PubSubClient.h, ArduinoJson.h, ArduinoHttpClient.h',
            self::Esp32 => 'WiFi.h, PubSubClient.h, ArduinoJson.h, HTTPClient.h',
            self::Esp8266 => 'ESP8266WiFi.h, PubSubClient.h, ArduinoJson.h, ESP8266HTTPClient.h',
            self::Stm32 => 'STM32WiFi.h, PubSubClient.h, ArduinoJson.h, HttpClient.h',
            self::Pico => 'WiFi.h, PubSubClient.h, ArduinoJson.h, HTTPClient.h',
            self::RaspberryPi => 'paho-mqtt, requests, json (Python)',
            self::Generic => 'WiFi.h, PubSubClient.h, ArduinoJson.h, HTTPClient.h',
        };
    }
}
