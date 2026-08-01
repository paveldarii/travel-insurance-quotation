<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
final class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'base_currency_code' => Currency::EUR,
            'currency_code' => Currency::EUR,

            'quoted_on' => now()->toDateString(),
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-30',

            'trip_days' => 30,

            'fixed_daily_rate_base_minor' => 300,
            'total_base_minor' => 11_700,
            'total_minor' => 11_700,
        ];
    }
}
