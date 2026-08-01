<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QuotationTravelerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quotation_id',
    'full_name',
    'date_of_birth',
    'age_at_trip_start',
    'age_load_basis_points',
    'subtotal_base_minor',
    'subtotal_minor',
])]
final class QuotationTraveler extends Model
{
    /** @use HasFactory<QuotationTravelerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'immutable_date',
            'age_at_trip_start' => 'integer',
            'age_load_basis_points' => 'integer',
            'subtotal_base_minor' => 'integer',
            'subtotal_minor' => 'integer',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
