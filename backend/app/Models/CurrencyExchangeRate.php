<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'base_currency_code',
    'quote_currency_code',
    'rate_date',
    'rate',
    'source',
    'retrieved_at',
])]
final class CurrencyExchangeRate extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_currency_code' => Currency::class,
            'quote_currency_code' => Currency::class,
            'rate_date' => 'immutable_date',
            'rate' => 'decimal:10',
            'retrieved_at' => 'immutable_datetime',
        ];
    }

    public function quotationSnapshots(): HasMany
    {
        return $this->hasMany(
            QuotationExchangeRate::class,
            'currency_exchange_rate_id',
        );
    }
}
