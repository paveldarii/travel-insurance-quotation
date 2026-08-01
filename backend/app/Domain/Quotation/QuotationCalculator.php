<?php

declare(strict_types=1);

namespace App\Domain\Quotation;

use App\Data\QuotationCalculation;
use App\Data\TravelerCalculation;
use App\Data\TravelerInput;
use App\Services\ResolvedExchangeRate;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Carbon\CarbonImmutable;
use DomainException;

final readonly class QuotationCalculator
{
    private const FIXED_DAILY_RATE_BASE_MINOR = 300;

    private const BASIS_POINT_DIVISOR = 10_000;

    public function __construct(
        private TravelerAgeResolver $ageResolver,
        private AgeLoadResolver $ageLoadResolver,
    ) {}

    /**
     * @param list<TravelerInput> $travelers
     */
    public function calculate(
        array $travelers,
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        ResolvedExchangeRate $exchangeRate,
    ): QuotationCalculation {
        if ($travelers === []) {
            throw new DomainException(
                'At least one traveler is required.'
            );
        }

        if ($endDate->isBefore($startDate)) {
            throw new DomainException(
                'The trip end date cannot be before the start date.'
            );
        }

        $tripDays = (int) $startDate->diffInDays($endDate) + 1;

        $travelerCalculations = [];
        $totalBaseMinor = 0;
        $totalMinor = 0;

        foreach ($travelers as $traveler) {
            $age = $this->ageResolver->resolve(
                $traveler->dateOfBirth,
                $startDate,
            );

            $loadBasisPoints = $this->ageLoadResolver
                ->resolveBasisPoints($age);

            $subtotalBaseMinor = $this->calculateBaseSubtotalMinor(
                loadBasisPoints: $loadBasisPoints,
                tripDays: $tripDays,
            );

            $subtotalMinor = $this->convertMinorUnits(
                baseMinor: $subtotalBaseMinor,
                rate: $exchangeRate->rate,
            );

            $totalBaseMinor += $subtotalBaseMinor;
            $totalMinor += $subtotalMinor;

            $travelerCalculations[] = new TravelerCalculation(
                fullName: $traveler->fullName,
                dateOfBirth: $traveler->dateOfBirth,
                ageAtTripStart: $age,
                ageLoadBasisPoints: $loadBasisPoints,
                subtotalBaseMinor: $subtotalBaseMinor,
                subtotalMinor: $subtotalMinor,
            );
        }

        return new QuotationCalculation(
            tripDays: $tripDays,
            totalBaseMinor: $totalBaseMinor,
            totalMinor: $totalMinor,
            travelers: $travelerCalculations,
        );
    }

    public function fixedDailyRateBaseMinor(): int
    {
        return self::FIXED_DAILY_RATE_BASE_MINOR;
    }

    private function calculateBaseSubtotalMinor(
        int $loadBasisPoints,
        int $tripDays,
    ): int {
        $numerator = self::FIXED_DAILY_RATE_BASE_MINOR
            * $loadBasisPoints
            * $tripDays;

        return intdiv(
            $numerator,
            self::BASIS_POINT_DIVISOR,
        );
    }

    private function convertMinorUnits(
        int $baseMinor,
        string $rate,
    ): int {
        return BigDecimal::of($baseMinor)
            ->multipliedBy($rate)
            ->toScale(
                scale: 0,
                roundingMode: RoundingMode::HalfUp,
            )
            ->toInt();
    }
}
