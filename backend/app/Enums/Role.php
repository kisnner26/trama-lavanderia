<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Reception = 'reception';
    case Operator = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'propietario',
            self::Reception => 'recepción',
            self::Operator => 'operación',
        };
    }

    public function canReceive(): bool
    {
        return $this !== self::Operator;
    }
}
