<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quotation
 */
final class QuotationResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'quotation_id' => $this->public_id,

            'total' => $this->formatMinor(
                $this->total_minor,
            ),

            'currency_id' =>
            $this->currency_code->value,

            'base_total' => $this->formatMinor(
                $this->total_base_minor,
            ),

            'base_currency_id' =>
            $this->base_currency_code->value,

            'quoted_on' =>
            $this->quoted_on->toDateString(),

            'start_date' =>
            $this->start_date->toDateString(),

            'end_date' =>
            $this->end_date->toDateString(),

            'trip_days' => $this->trip_days,

            'exchange_rate' => [
                'base_currency_id' =>
                $this->exchangeRate
                    ->base_currency_code
                    ->value,

                'quote_currency_id' =>
                $this->exchangeRate
                    ->quote_currency_code
                    ->value,

                'rate_date' =>
                $this->exchangeRate
                    ->rate_date
                    ->toDateString(),

                'rate' =>
                (string) $this->exchangeRate->rate,
            ],

            'travelers' => $this->travelers
                ->map(
                    fn($traveler): array => [
                        'full_name' =>
                        $traveler->full_name,

                        'date_of_birth' =>
                        $traveler
                            ->date_of_birth
                            ->toDateString(),

                        'age_at_trip_start' =>
                        $traveler
                            ->age_at_trip_start,

                        'subtotal' =>
                        $this->formatMinor(
                            $traveler
                                ->subtotal_minor,
                        ),
                    ],
                )
                ->values()
                ->all(),
        ];
    }

    private function formatMinor(
        int $minor,
    ): string {
        return number_format(
            $minor / 100,
            2,
            '.',
            '',
        );
    }
}
