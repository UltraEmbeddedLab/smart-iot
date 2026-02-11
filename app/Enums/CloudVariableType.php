<?php declare(strict_types=1);

namespace App\Enums;

enum CloudVariableType: string
{
    case Int = 'int';
    case Float = 'float';
    case Boolean = 'boolean';
    case String = 'string';
    case Temperature = 'temperature';
    case Humidity = 'humidity';
    case Luminosity = 'luminosity';
    case Percentage = 'percentage';
    case Voltage = 'voltage';
    case Current = 'current';
    case Power = 'power';
    case Pressure = 'pressure';
    case Speed = 'speed';
    case Location = 'location';
    case Color = 'color';
    case Switch = 'switch';
    case DimmedLight = 'dimmed_light';

    public function label(): string
    {
        return match ($this) {
            self::Int => 'Integer',
            self::Float => 'Float',
            self::Boolean => 'Boolean',
            self::String => 'String',
            self::Temperature => 'Temperature',
            self::Humidity => 'Humidity',
            self::Luminosity => 'Luminosity',
            self::Percentage => 'Percentage',
            self::Voltage => 'Voltage',
            self::Current => 'Current',
            self::Power => 'Power',
            self::Pressure => 'Pressure',
            self::Speed => 'Speed',
            self::Location => 'Location',
            self::Color => 'Color',
            self::Switch => 'Switch',
            self::DimmedLight => 'Dimmed Light',
        };
    }

    public function declarationType(): string
    {
        return match ($this) {
            self::Int => 'int',
            self::Float => 'float',
            self::Boolean => 'bool',
            self::String => 'String',
            self::Temperature => 'CloudTemperature',
            self::Humidity => 'CloudHumidity',
            self::Luminosity => 'CloudLuminosity',
            self::Percentage => 'CloudPercentage',
            self::Voltage => 'CloudVoltage',
            self::Current => 'CloudCurrent',
            self::Power => 'CloudPower',
            self::Pressure => 'CloudPressure',
            self::Speed => 'CloudSpeed',
            self::Location => 'CloudLocation',
            self::Color => 'CloudColor',
            self::Switch => 'CloudSwitch',
            self::DimmedLight => 'CloudDimmedLight',
        };
    }
}
