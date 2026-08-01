<?php

declare(strict_types=1);

namespace App\Domain\Quotation;

use Carbon\CarbonImmutable;
use DomainException;

final class TravelerAgeResolver
{
    public function resolve(
        CarbonImmutable $dateOfBirth,
        CarbonImmutable $tripStartDate,
    ): int {
        if ($dateOfBirth->isAfter($tripStartDate)) {
            throw new DomainException(
                'The traveler date of birth cannot be after the trip start date.'
            );
        }

        return (int) $dateOfBirth->diffInYears(
            $tripStartDate,
            absolute: true,
        );
    }
}
