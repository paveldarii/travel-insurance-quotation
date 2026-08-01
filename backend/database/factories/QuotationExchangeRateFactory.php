<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationExchangeRate>
 */
final class QuotationExchangeRateFactory extends Factory
{
    protected $model = QuotationExchangeRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quotation_id' => Quotation::factory(),
            'currency_exchange_rate_id' => null,
            'base_currency_code' => 'EUR',
            'quote_currency_code' => 'EUR',
            'rate_date' => now()->toDateString(),
            'rate' => '1.0000000000',
            'source' => 'FACTORY',
        ];
    }
}
