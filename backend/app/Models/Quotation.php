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
use RuntimeException;

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

    private const int PUBLIC_ID_LENGTH = 8;

    private const int PUBLIC_ID_GENERATION_ATTEMPTS = 10;

    private const string PUBLIC_ID_ALPHABET =
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * @return array<string, string>
     */
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

    protected static function booted(): void
    {
        static::creating(
            static function (Quotation $quotation): void {
                if (
                    is_string($quotation->public_id)
                    && $quotation->public_id !== ''
                ) {
                    return;
                }

                $quotation->public_id =
                    self::generateUniquePublicId();
            }
        );
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
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

    private static function generateUniquePublicId(): string
    {
        for (
            $attempt = 1;
            $attempt <= self::PUBLIC_ID_GENERATION_ATTEMPTS;
            $attempt++
        ) {
            $publicId = self::generatePublicId();

            $alreadyExists = self::query()
                ->where('public_id', $publicId)
                ->exists();

            if (! $alreadyExists) {
                return $publicId;
            }
        }

        throw new RuntimeException(
            'Unable to generate a unique quotation public ID.'
        );
    }

    private static function generatePublicId(): string
    {
        $publicId = '';
        $maximumIndex = strlen(self::PUBLIC_ID_ALPHABET) - 1;

        for (
            $position = 0;
            $position < self::PUBLIC_ID_LENGTH;
            $position++
        ) {
            $publicId .= self::PUBLIC_ID_ALPHABET[random_int(0, $maximumIndex)];
        }

        return $publicId;
    }
}
