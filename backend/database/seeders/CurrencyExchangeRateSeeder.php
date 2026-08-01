<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Currency;
use App\Models\CurrencyExchangeRate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

final class CurrencyExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = CarbonImmutable::today();
        $endDate = $startDate->addMonth();

        for (
            $rateDate = $startDate;
            $rateDate->lessThanOrEqualTo($endDate);
            $rateDate = $rateDate->addDay()
        ) {
            $this->seedRate(
                quoteCurrency: Currency::USD,
                rateDate: $rateDate,
                rate: '1.1000000000',
            );

            $this->seedRate(
                quoteCurrency: Currency::GBP,
                rateDate: $rateDate,
                rate: '0.8500000000',
            );
        }
    }

    private function seedRate(
        Currency $quoteCurrency,
        CarbonImmutable $rateDate,
        string $rate,
    ): void {
        CurrencyExchangeRate::query()->updateOrCreate(
            [
                'base_currency_code' => Currency::EUR->value,
                'quote_currency_code' => $quoteCurrency->value,
                'rate_date' => $rateDate->toDateString(),
            ],
            [
                'rate' => $rate,
                'source' => 'DEVELOPMENT_SEED',
                'retrieved_at' => now(),
            ],
        );
    }
}
