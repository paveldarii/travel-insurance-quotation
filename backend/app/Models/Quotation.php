<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'base_currency_code',
    'currency_code',
    'quoted_on',
    'start_date',
    'end_date',
    'trip_days',
    'fixed_daily_rate_base_minor',
    'total_base_minor',
    'total_minor',
])]
final class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'base_currency_code' => Currency::class,
            'currency_code' => Currency::class,
            'quoted_on' => 'immutable_date',
            'start_date' => 'immutable_date',
            'end_date' => 'immutable_date',
            'trip_days' => 'integer',
            'fixed_daily_rate_base_minor' => 'integer',
            'total_base_minor' => 'integer',
            'total_minor' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function travelers(): HasMany
    {
        return $this->hasMany(QuotationTraveler::class);
    }

    public function exchangeRate(): HasOne
    {
        return $this->hasOne(QuotationExchangeRate::class);
    }
}
