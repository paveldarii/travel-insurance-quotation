<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Currency;
use Carbon\CarbonImmutable;

final readonly class ResolvedExchangeRate
{
    public function __construct(
        public ?int $dailyRateId,
        public Currency $baseCurrency,
        public Currency $quoteCurrency,
        public CarbonImmutable $rateDate,
        public string $rate,
        public string $source,
    ) {}
}
