<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Database\Factories\QuotationExchangeRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quotation_id',
    'currency_exchange_rate_id',
    'base_currency_code',
    'quote_currency_code',
    'rate_date',
    'rate',
    'source',
])]
final class QuotationExchangeRate extends Model
{
    /** @use HasFactory<QuotationExchangeRateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'base_currency_code' => Currency::class,
            'quote_currency_code' => Currency::class,
            'rate_date' => 'immutable_date',
            'rate' => 'decimal:10',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function dailyRate(): BelongsTo
    {
        return $this->belongsTo(
            CurrencyExchangeRate::class,
            'currency_exchange_rate_id',
        );
    }
}
