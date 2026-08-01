<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TravelerInput;
use App\Domain\Quotation\QuotationCalculator;
use App\Enums\Currency;
use App\Models\Quotation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class CreateQuotationService
{
    public function __construct(
        private ResolveExchangeRateService $exchangeRateResolver,
        private QuotationCalculator $calculator,
    ) {}

    /**
     * @param array{
     *     currency_id: string,
     *     start_date: string,
     *     end_date: string,
     *     travelers: list<array{
     *         full_name: string,
     *         date_of_birth: string
     *     }>
     * } $data
     */
    public function execute(User $user, array $data): Quotation
    {
        $quotedOn = CarbonImmutable::today();

        $startDate = CarbonImmutable::createFromFormat(
            'Y-m-d',
            $data['start_date'],
        )->startOfDay();

        $endDate = CarbonImmutable::createFromFormat(
            'Y-m-d',
            $data['end_date'],
        )->startOfDay();

        $currency = Currency::from($data['currency_id']);

        $exchangeRate = $this->exchangeRateResolver->execute(
            targetCurrency: $currency,
            rateDate: $quotedOn,
        );

        $travelers = array_map(
            static fn(array $traveler): TravelerInput => new TravelerInput(
                fullName: $traveler['full_name'],
                dateOfBirth: CarbonImmutable::createFromFormat(
                    'Y-m-d',
                    $traveler['date_of_birth'],
                )->startOfDay(),
            ),
            $data['travelers'],
        );

        $calculation = $this->calculator->calculate(
            travelers: $travelers,
            startDate: $startDate,
            endDate: $endDate,
            exchangeRate: $exchangeRate,
        );

        return DB::transaction(
            function () use (
                $user,
                $currency,
                $quotedOn,
                $startDate,
                $endDate,
                $exchangeRate,
                $calculation,
            ): Quotation {
                $quotation = $user->quotations()->create([
                    'base_currency_code' => Currency::EUR,
                    'currency_code' => $currency,
                    'quoted_on' => $quotedOn,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'trip_days' => $calculation->tripDays,
                    'fixed_daily_rate_base_minor' =>
                    $this->calculator->fixedDailyRateBaseMinor(),
                    'total_base_minor' => $calculation->totalBaseMinor,
                    'total_minor' => $calculation->totalMinor,
                ]);

                $quotation->travelers()->createMany(
                    array_map(
                        static fn($traveler): array => [
                            'full_name' => $traveler->fullName,
                            'date_of_birth' => $traveler->dateOfBirth,
                            'age_at_trip_start' =>
                            $traveler->ageAtTripStart,
                            'age_load_basis_points' =>
                            $traveler->ageLoadBasisPoints,
                            'subtotal_base_minor' =>
                            $traveler->subtotalBaseMinor,
                            'subtotal_minor' =>
                            $traveler->subtotalMinor,
                        ],
                        $calculation->travelers,
                    ),
                );

                $quotation->exchangeRate()->create([
                    'currency_exchange_rate_id' =>
                    $exchangeRate->dailyRateId,
                    'base_currency_code' =>
                    $exchangeRate->baseCurrency,
                    'quote_currency_code' =>
                    $exchangeRate->quoteCurrency,
                    'rate_date' => $exchangeRate->rateDate,
                    'rate' => $exchangeRate->rate,
                    'source' => $exchangeRate->source,
                ]);

                return $quotation->load([
                    'travelers',
                    'exchangeRate',
                ]);
            },
            attempts: 3,
        );
    }
}
