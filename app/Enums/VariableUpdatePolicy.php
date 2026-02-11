<?php declare(strict_types=1);

namespace App\Enums;

enum VariableUpdatePolicy: string
{
    case OnChange = 'on_change';
    case Periodically = 'periodically';

    public function label(): string
    {
        return match ($this) {
            self::OnChange => 'On Change',
            self::Periodically => 'Periodically',
        };
    }
}
