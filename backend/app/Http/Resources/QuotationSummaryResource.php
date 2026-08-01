<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Quotation
 */
final class QuotationSummaryResource extends JsonResource
{
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
            'currency_id' => $this->currency_code->value,
            'quoted_on' => $this->quoted_on->toDateString(),
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'trip_days' => $this->trip_days,
            'travelers_count' => (int) $this->travelers_count,
        ];
    }

    private function formatMinor(int $minor): string
    {
        return number_format(
            $minor / 100,
            2,
            '.',
            '',
        );
    }
}
