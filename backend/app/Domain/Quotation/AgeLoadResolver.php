<?php

declare(strict_types=1);

namespace App\Domain\Quotation;

use DomainException;

final class AgeLoadResolver
{
    public function resolveBasisPoints(int $age): int
    {
        $ageLoads = config('quotation.age_loads');

        if (! is_array($ageLoads)) {
            throw new DomainException(
                'Quotation age-load configuration is invalid.',
            );
        }

        foreach ($ageLoads as $ageLoad) {
            if (! is_array($ageLoad)) {
                continue;
            }

            $minimumAge = $ageLoad['minimum_age'] ?? null;
            $maximumAge = $ageLoad['maximum_age'] ?? null;
            $basisPoints = $ageLoad['basis_points'] ?? null;

            if (
                ! is_int($minimumAge)
                || ! is_int($maximumAge)
                || ! is_int($basisPoints)
            ) {
                continue;
            }

            if (
                $age >= $minimumAge
                && $age <= $maximumAge
            ) {
                return $basisPoints;
            }
        }

        throw new DomainException(
            "Traveler age {$age} is not eligible for quotation.",
        );
    }
}
