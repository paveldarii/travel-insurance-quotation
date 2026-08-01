<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationTraveler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationTraveler>
 */
final class QuotationTravelerFactory extends Factory
{
    protected $model = QuotationTraveler::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quotation_id' => Quotation::factory(),
            'full_name' => fake()->name(),
            'date_of_birth' => '1998-05-10',
            'age_at_trip_start' => 28,
            'age_load_basis_points' => 6000,
            'subtotal_base_minor' => 5400,
            'subtotal_minor' => 5400,
        ];
    }
}
