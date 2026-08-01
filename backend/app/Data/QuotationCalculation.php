<?php

declare(strict_types=1);

namespace App\Data;

final readonly class QuotationCalculation
{
    /**
     * @param list<TravelerCalculation> $travelers
     */
    public function __construct(
        public int $tripDays,
        public int $totalBaseMinor,
        public int $totalMinor,
        public array $travelers,
    ) {}
}
