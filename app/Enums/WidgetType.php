<?php declare(strict_types=1);

namespace App\Enums;

enum WidgetType: string
{
    // MVP (implemented)
    case Value = 'value';
    case Switch = 'switch';
    case Led = 'led';
    case Gauge = 'gauge';
    case Slider = 'slider';
    case Chart = 'chart';

    // Future stubs
    case Map = 'map';
    case Color = 'color';
    case Messenger = 'messenger';
    case Scheduler = 'scheduler';
    case Percentage = 'percentage';
    case Image = 'image';
    case Sticky = 'sticky';

    /**
     * @return list<self>
     */
    public static function mvpCases(): array
    {
        return [
            self::Value,
            self::Switch,
            self::Led,
            self::Gauge,
            self::Slider,
            self::Chart,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Value => 'Value',
            self::Switch => 'Switch',
            self::Led => 'LED',
            self::Gauge => 'Gauge',
            self::Slider => 'Slider',
            self::Chart => 'Chart',
            self::Map => 'Map',
            self::Color => 'Color',
            self::Messenger => 'Messenger',
            self::Scheduler => 'Scheduler',
            self::Percentage => 'Percentage',
            self::Image => 'Image',
            self::Sticky => 'Sticky Note',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Value => 'hashtag',
            self::Switch => 'bolt',
            self::Led => 'sun',
            self::Gauge => 'chart-bar',
            self::Slider => 'adjustments-horizontal',
            self::Chart => 'chart-bar-square',
            self::Map => 'map-pin',
            self::Color => 'swatch',
            self::Messenger => 'chat-bubble-left-right',
            self::Scheduler => 'clock',
            self::Percentage => 'chart-pie',
            self::Image => 'photo',
            self::Sticky => 'document-text',
        };
    }

    public function isWritable(): bool
    {
        return match ($this) {
            self::Switch, self::Slider => true,
            default => false,
        };
    }

    public function isImplemented(): bool
    {
        return in_array($this, self::mvpCases(), true);
    }
}
