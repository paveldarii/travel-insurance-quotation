<?php

declare(strict_types=1);

namespace Tests\Feature\Quotation;

use App\Enums\Currency;
use App\Models\CurrencyExchangeRate;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

final class CreateQuotationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_authenticated_user_can_create_eur_quotation(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-08-01 10:00:00'),
        );

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this
            ->withToken($token)
            ->postJson('/api/quotation', [
                'currency_id' => 'EUR',
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-30',
                'travelers' => [
                    [
                        'full_name' => 'Pavel Darii',
                        'date_of_birth' => '1998-05-10',
                    ],
                    [
                        'full_name' => 'Jane Darii',
                        'date_of_birth' => '1991-03-15',
                    ],
                ],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('total', '117.00')
            ->assertJsonPath('currency_id', 'EUR')
            ->assertJsonPath('trip_days', 30)
            ->assertJsonCount(2, 'travelers');

        $publicId = $response->json('quotation_id');

        self::assertIsString($publicId);
        self::assertMatchesRegularExpression(
            '/^[A-Z0-9]{8}$/',
            $publicId,
        );

        $this->assertDatabaseHas('quotations', [
            'public_id' => $publicId,
            'user_id' => $user->id,
            'currency_code' => 'EUR',
            'total_base_minor' => 11_700,
            'total_minor' => 11_700,
        ]);

        $this->assertDatabaseCount(
            'quotation_travelers',
            2,
        );

        $this->assertDatabaseHas(
            'quotation_travelers',
            [
                'full_name' => 'Pavel Darii',
                'age_at_trip_start' => 28,
                'subtotal_minor' => 5_400,
            ],
        );

        $this->assertDatabaseHas(
            'quotation_exchange_rates',
            [
                'base_currency_code' => 'EUR',
                'quote_currency_code' => 'EUR',
                'rate' => '1.0000000000',
            ],
        );
    }

    public function test_it_creates_usd_quotation_using_daily_rate(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-08-01 10:00:00'),
        );

        CurrencyExchangeRate::query()->create([
            'base_currency_code' => Currency::EUR,
            'quote_currency_code' => Currency::USD,
            'rate_date' => '2026-08-01',
            'rate' => '1.1000000000',
            'source' => 'TEST',
            'retrieved_at' => now(),
        ]);

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this
            ->withToken($token)
            ->postJson('/api/quotation', [
                'currency_id' => 'USD',
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-30',
                'travelers' => [
                    [
                        'full_name' => 'Pavel Darii',
                        'date_of_birth' => '1998-05-10',
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('total', '59.40')
            ->assertJsonPath('currency_id', 'USD')
            ->assertJsonPath(
                'exchange_rate.rate',
                '1.1000000000',
            );

        $this->assertDatabaseHas('quotations', [
            'currency_code' => 'USD',
            'total_base_minor' => 5_400,
            'total_minor' => 5_940,
        ]);
    }

    public function test_quotation_requires_authentication(): void
    {
        $this
            ->postJson('/api/quotation', [])
            ->assertUnauthorized();
    }

    public function test_quotation_belongs_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this
            ->withToken($token)
            ->postJson('/api/quotation', [
                'currency_id' => 'EUR',
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-30',
                'travelers' => [
                    [
                        'full_name' => 'Pavel Darii',
                        'date_of_birth' => '1998-05-10',
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('quotations', [
            'user_id' => $user->id,
        ]);
    }
}
