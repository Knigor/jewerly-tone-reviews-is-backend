<?php

namespace App\Enum;

enum ProductSize: int
{
    case SIZE_14 = 14;
    case SIZE_15 = 15;
    case SIZE_16 = 16;
    case SIZE_17 = 17;
    case SIZE_18 = 18;
    case SIZE_19 = 19;
    case SIZE_20 = 20;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}