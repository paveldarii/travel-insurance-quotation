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

final class QuotationCalculator
{
    private const int BASIS_POINTS_DIVISOR = 10_000;

    public function __construct(
        private readonly TravelerAgeResolver $ageResolver,
        private readonly AgeLoadResolver $ageLoadResolver,
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
                'At least one traveler is required.',
            );
        }

        if ($endDate->lessThan($startDate)) {
            throw new DomainException(
                'The trip end date cannot precede the start date.',
            );
        }

        /*
         * Carbon may return the date difference as a float.
         * Brick Math accepts only int, string, or BigNumber,
         * so the value must be converted explicitly.
         */
        $tripDays = (int) $startDate->diffInDays(
            $endDate,
            absolute: true,
        );

        /*
         * Both the start date and end date are included.
         */
        $tripDays++;

        $fixedDailyRateBaseMinor =
            $this->fixedDailyRateBaseMinor();

        $travelerCalculations = [];
        $totalBaseMinor = 0;
        $totalMinor = 0;

        foreach ($travelers as $traveler) {
            $age = $this->ageResolver->resolve(
                $traveler->dateOfBirth,
                $startDate,
            );

            $ageLoadBasisPoints = (int) $this
                ->ageLoadResolver
                ->resolveBasisPoints($age);

            /*
             * Base subtotal:
             *
             * fixed daily rate
             * × inclusive trip days
             * × age-load basis points
             * ÷ 10,000
             */
            $subtotalBaseMinor = BigDecimal::of(
                $fixedDailyRateBaseMinor,
            )
                ->multipliedBy($tripDays)
                ->multipliedBy($ageLoadBasisPoints)
                ->dividedBy(
                    self::BASIS_POINTS_DIVISOR,
                    0,
                    RoundingMode::HalfUp,
                )
                ->toInt();

            /*
             * Convert the base-currency subtotal into
             * the requested currency.
             */
            $subtotalMinor = BigDecimal::of(
                $subtotalBaseMinor,
            )
                ->multipliedBy($exchangeRate->rate)
                ->toScale(
                    0,
                    RoundingMode::HalfUp,
                )
                ->toInt();

            $travelerCalculations[] =
                new TravelerCalculation(
                    fullName: $traveler->fullName,
                    dateOfBirth: $traveler->dateOfBirth,
                    ageAtTripStart: $age,
                    ageLoadBasisPoints: $ageLoadBasisPoints,
                    subtotalBaseMinor: $subtotalBaseMinor,
                    subtotalMinor: $subtotalMinor,
                );

            $totalBaseMinor += $subtotalBaseMinor;
            $totalMinor += $subtotalMinor;
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
        $rate = config(
            'quotation.fixed_daily_rate_minor',
        );

        if (! is_int($rate) || $rate <= 0) {
            throw new DomainException(
                'The quotation daily rate configuration is invalid.',
            );
        }

        return $rate;
    }
}
