<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class TravelerCalculation
{
    public function __construct(
        public string $fullName,
        public CarbonImmutable $dateOfBirth,
        public int $ageAtTripStart,
        public int $ageLoadBasisPoints,
        public int $subtotalBaseMinor,
        public int $subtotalMinor,
    ) {}
}
