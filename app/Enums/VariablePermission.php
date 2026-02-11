<?php declare(strict_types=1);

namespace App\Enums;

enum VariablePermission: string
{
    case ReadOnly = 'read_only';
    case ReadWrite = 'read_write';

    public function label(): string
    {
        return match ($this) {
            self::ReadOnly => 'Read Only',
            self::ReadWrite => 'Read & Write',
        };
    }
}
