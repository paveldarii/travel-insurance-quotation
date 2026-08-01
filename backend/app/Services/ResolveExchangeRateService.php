<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Currency;
use App\Models\CurrencyExchangeRate;
use Carbon\CarbonImmutable;
use DomainException;

final class ResolveExchangeRateService
{
    public function execute(
        Currency $targetCurrency,
        CarbonImmutable $rateDate,
    ): ResolvedExchangeRate {
        if ($targetCurrency === Currency::EUR) {
            return new ResolvedExchangeRate(
                dailyRateId: null,
                baseCurrency: Currency::EUR,
                quoteCurrency: Currency::EUR,
                rateDate: $rateDate,
                rate: '1.0000000000',
                source: 'BASE_CURRENCY',
            );
        }

        $rate = CurrencyExchangeRate::query()
            ->where('base_currency_code', Currency::EUR->value)
            ->where('quote_currency_code', $targetCurrency->value)
            ->whereDate('rate_date', $rateDate->toDateString())
            ->first();

        if (! $rate instanceof CurrencyExchangeRate) {
            throw new DomainException(sprintf(
                'No EUR to %s exchange rate is available for %s.',
                $targetCurrency->value,
                $rateDate->toDateString(),
            ));
        }

        return new ResolvedExchangeRate(
            dailyRateId: $rate->id,
            baseCurrency: Currency::EUR,
            quoteCurrency: $targetCurrency,
            rateDate: $rateDate,
            rate: $rate->rate,
            source: $rate->source,
        );
    }
}
