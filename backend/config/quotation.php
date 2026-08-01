<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Base currency
    |--------------------------------------------------------------------------
    */

    'base_currency' => env(
        'QUOTATION_BASE_CURRENCY',
        'EUR',
    ),

    /*
    |--------------------------------------------------------------------------
    | Fixed daily rate
    |--------------------------------------------------------------------------
    |
    | Stored in minor currency units.
    | 300 represents EUR 3.00.
    */

    'fixed_daily_rate_minor' => (int) env(
        'QUOTATION_FIXED_DAILY_RATE_MINOR',
        300,
    ),

    /*
    |--------------------------------------------------------------------------
    | Eligible traveler ages
    |--------------------------------------------------------------------------
    */

    'minimum_age' => (int) env(
        'QUOTATION_MINIMUM_AGE',
        18,
    ),

    'maximum_age' => (int) env(
        'QUOTATION_MAXIMUM_AGE',
        70,
    ),

    /*
    |--------------------------------------------------------------------------
    | Age-load rules
    |--------------------------------------------------------------------------
    |
    | Loads are stored in basis points.
    |
    | 6000  = 0.60
    | 7000  = 0.70
    | 8000  = 0.80
    | 9000  = 0.90
    | 10000 = 1.00
    */

    'age_loads' => [
        [
            'minimum_age' => 18,
            'maximum_age' => 30,
            'basis_points' => (int) env(
                'QUOTATION_AGE_LOAD_18_30',
                6000,
            ),
        ],
        [
            'minimum_age' => 31,
            'maximum_age' => 40,
            'basis_points' => (int) env(
                'QUOTATION_AGE_LOAD_31_40',
                7000,
            ),
        ],
        [
            'minimum_age' => 41,
            'maximum_age' => 50,
            'basis_points' => (int) env(
                'QUOTATION_AGE_LOAD_41_50',
                8000,
            ),
        ],
        [
            'minimum_age' => 51,
            'maximum_age' => 60,
            'basis_points' => (int) env(
                'QUOTATION_AGE_LOAD_51_60',
                9000,
            ),
        ],
        [
            'minimum_age' => 61,
            'maximum_age' => 70,
            'basis_points' => (int) env(
                'QUOTATION_AGE_LOAD_61_70',
                10000,
            ),
        ],
    ],
];
