<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case GBP = 'GBP';
    case USD = 'USD';

    public static function base(): self
    {
        return self::EUR;
    }

    public function minorUnitScale(): int
    {
        return 2;
    }
}
