<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Quotation;

use App\Data\TravelerInput;
use App\Domain\Quotation\AgeLoadResolver;
use App\Domain\Quotation\QuotationCalculator;
use App\Domain\Quotation\TravelerAgeResolver;
use App\Enums\Currency;
use App\Services\ResolvedExchangeRate;
use Carbon\CarbonImmutable;
use DomainException;
use Tests\TestCase;

final class QuotationCalculatorTest extends TestCase
{
    private QuotationCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'quotation.fixed_daily_rate_minor',
            300,
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

        $this->calculator = new QuotationCalculator(
            new TravelerAgeResolver(),
            new AgeLoadResolver(),
        );
    }

    public function test_it_calculates_documented_eur_example(): void
    {
        $result = $this->calculator->calculate(
            travelers: [
                new TravelerInput(
                    fullName: 'Traveler One',
                    dateOfBirth: CarbonImmutable::parse(
                        '1998-05-10',
                    ),
                ),
                new TravelerInput(
                    fullName: 'Traveler Two',
                    dateOfBirth: CarbonImmutable::parse(
                        '1991-03-15',
                    ),
                ),
            ],
            startDate: CarbonImmutable::parse(
                '2026-10-01',
            ),
            endDate: CarbonImmutable::parse(
                '2026-10-30',
            ),
            exchangeRate: $this->eurRate(),
        );

        self::assertSame(30, $result->tripDays);
        self::assertSame(
            11700,
            $result->totalBaseMinor,
        );
        self::assertSame(
            11700,
            $result->totalMinor,
        );
        self::assertCount(2, $result->travelers);

        self::assertSame(
            5400,
            $result->travelers[0]->subtotalBaseMinor,
        );

        self::assertSame(
            6300,
            $result->travelers[1]->subtotalBaseMinor,
        );
    }

    public function test_same_start_and_end_date_counts_as_one_day(): void
    {
        $result = $this->calculator->calculate(
            travelers: [
                new TravelerInput(
                    fullName: 'Traveler One',
                    dateOfBirth: CarbonImmutable::parse(
                        '1998-05-10',
                    ),
                ),
            ],
            startDate: CarbonImmutable::parse(
                '2026-10-01',
            ),
            endDate: CarbonImmutable::parse(
                '2026-10-01',
            ),
            exchangeRate: $this->eurRate(),
        );

        self::assertSame(1, $result->tripDays);
        self::assertSame(180, $result->totalMinor);
    }

    public function test_it_converts_eur_to_usd(): void
    {
        $result = $this->calculator->calculate(
            travelers: [
                new TravelerInput(
                    fullName: 'Traveler One',
                    dateOfBirth: CarbonImmutable::parse(
                        '1998-05-10',
                    ),
                ),
            ],
            startDate: CarbonImmutable::parse(
                '2026-10-01',
            ),
            endDate: CarbonImmutable::parse(
                '2026-10-30',
            ),
            exchangeRate: new ResolvedExchangeRate(
                dailyRateId: 1,
                baseCurrency: Currency::EUR,
                quoteCurrency: Currency::USD,
                rateDate: CarbonImmutable::parse(
                    '2026-08-01',
                ),
                rate: '1.1000000000',
                source: 'TEST',
            ),
        );

        self::assertSame(
            5400,
            $result->totalBaseMinor,
        );

        self::assertSame(
            5940,
            $result->totalMinor,
        );
    }

    public function test_it_rejects_empty_traveler_list(): void
    {
        $this->expectException(DomainException::class);

        $this->calculator->calculate(
            travelers: [],
            startDate: CarbonImmutable::parse(
                '2026-10-01',
            ),
            endDate: CarbonImmutable::parse(
                '2026-10-30',
            ),
            exchangeRate: $this->eurRate(),
        );
    }

    public function test_it_rejects_end_date_before_start_date(): void
    {
        $this->expectException(DomainException::class);

        $this->calculator->calculate(
            travelers: [
                new TravelerInput(
                    fullName: 'Traveler One',
                    dateOfBirth: CarbonImmutable::parse(
                        '1998-05-10',
                    ),
                ),
            ],
            startDate: CarbonImmutable::parse(
                '2026-10-30',
            ),
            endDate: CarbonImmutable::parse(
                '2026-10-01',
            ),
            exchangeRate: $this->eurRate(),
        );
    }

    private function eurRate(): ResolvedExchangeRate
    {
        return new ResolvedExchangeRate(
            dailyRateId: null,
            baseCurrency: Currency::EUR,
            quoteCurrency: Currency::EUR,
            rateDate: CarbonImmutable::parse(
                '2026-08-01',
            ),
            rate: '1.0000000000',
            source: 'BASE_CURRENCY',
        );
    }
}
