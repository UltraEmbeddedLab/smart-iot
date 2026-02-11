<?php declare(strict_types=1);

namespace App\Enums;

enum TriggerOperator: string
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case GreaterThan = 'greater_than';
    case LessThan = 'less_than';
    case GreaterOrEqual = 'greater_or_equal';
    case LessOrEqual = 'less_or_equal';

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'Equals',
            self::NotEquals => 'Not Equals',
            self::GreaterThan => 'Greater Than',
            self::LessThan => 'Less Than',
            self::GreaterOrEqual => 'Greater or Equal',
            self::LessOrEqual => 'Less or Equal',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Equals => '==',
            self::NotEquals => '!=',
            self::GreaterThan => '>',
            self::LessThan => '<',
            self::GreaterOrEqual => '>=',
            self::LessOrEqual => '<=',
        };
    }
}
