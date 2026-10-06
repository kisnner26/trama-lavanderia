<?php

namespace App\Enums;

enum BillingUnit: string
{
    case Piece = 'piece';
    case Kilogram = 'kg';

    public function label(): string
    {
        return match ($this) {
            self::Piece => 'por pieza',
            self::Kilogram => 'por kg',
        };
    }
}
