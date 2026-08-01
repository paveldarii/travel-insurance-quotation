<?php

declare(strict_types=1);

namespace App\Domain\Quotation;

use DomainException;

final class AgeLoadResolver
{
    public function resolveBasisPoints(int $age): int
    {
        return match (true) {
            $age >= 18 && $age <= 30 => 6_000,
            $age >= 31 && $age <= 40 => 7_000,
            $age >= 41 && $age <= 50 => 8_000,
            $age >= 51 && $age <= 60 => 9_000,
            $age >= 61 && $age <= 70 => 10_000,

            default => throw new DomainException(
                'The traveler must be between 18 and 70 years old at the trip start date.'
            ),
        };
    }
}
