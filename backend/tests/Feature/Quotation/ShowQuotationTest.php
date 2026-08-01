<?php

declare(strict_types=1);

namespace Tests\Feature\Quotation;

use App\Models\Quotation;
use App\Models\QuotationExchangeRate;
use App\Models\QuotationTraveler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

final class ShowQuotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_quotation(): void
    {
        $user = User::factory()->create();

        $quotation = Quotation::factory()
            ->for($user)
            ->create();

        QuotationTraveler::factory()
            ->for($quotation)
            ->create();

        QuotationExchangeRate::factory()
            ->for($quotation)
            ->create();

        $token = JWTAuth::fromUser($user);

        $this
            ->withToken($token)
            ->getJson(
                "/api/quotations/{$quotation->public_id}",
            )
            ->assertOk()
            ->assertJsonPath(
                'quotation_id',
                $quotation->public_id,
            )
            ->assertJsonStructure([
                'quotation_id',
                'total',
                'currency_id',
                'base_total',
                'base_currency_id',
                'quoted_on',
                'start_date',
                'end_date',
                'trip_days',
                'exchange_rate' => [
                    'base_currency_id',
                    'quote_currency_id',
                    'rate_date',
                    'rate',
                ],
                'travelers' => [
                    [
                        'full_name',
                        'date_of_birth',
                        'age_at_trip_start',
                        'subtotal',
                    ],
                ],
            ]);
    }

    public function test_user_cannot_view_another_users_quotation(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $quotation = Quotation::factory()
            ->for($owner)
            ->create();

        $token = JWTAuth::fromUser($otherUser);

        $this
            ->withToken($token)
            ->getJson(
                "/api/quotations/{$quotation->public_id}",
            )
            ->assertNotFound();
    }

    public function test_unknown_quotation_returns_not_found(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this
            ->withToken($token)
            ->getJson('/api/quotations/AAAAAAAA')
            ->assertNotFound();
    }

    public function test_quotation_details_require_authentication(): void
    {
        $this
            ->getJson('/api/quotations/AAAAAAAA')
            ->assertUnauthorized();
    }
}
