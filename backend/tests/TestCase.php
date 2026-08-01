<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'quotation.base_currency',
            'EUR',
        );

        config()->set(
            'quotation.fixed_daily_rate_minor',
            300,
        );

        config()->set(
            'quotation.minimum_age',
            18,
        );

        config()->set(
            'quotation.maximum_age',
            70,
        );

        config()->set('quotation.age_loads', [
            [
                'minimum_age' => 18,
                'maximum_age' => 30,
                'basis_points' => 6000,
            ],
            [
                'minimum_age' => 31,
                'maximum_age' => 40,
                'basis_points' => 7000,
            ],
            [
                'minimum_age' => 41,
                'maximum_age' => 50,
                'basis_points' => 8000,
            ],
            [
                'minimum_age' => 51,
                'maximum_age' => 60,
                'basis_points' => 9000,
            ],
            [
                'minimum_age' => 61,
                'maximum_age' => 70,
                'basis_points' => 10000,
            ],
        ]);
    }
}
