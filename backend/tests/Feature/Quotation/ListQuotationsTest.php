<?php

declare(strict_types=1);

namespace Tests\Feature\Quotation;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

final class ListQuotationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_own_quotations(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownQuotation = Quotation::factory()
            ->for($user)
            ->create();

        Quotation::factory()
            ->for($otherUser)
            ->create();

        $token = JWTAuth::fromUser($user);

        $response = $this
            ->withToken($token)
            ->getJson('/api/quotations');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.quotation_id',
                $ownQuotation->public_id,
            )
            ->assertJsonStructure([
                'data' => [
                    [
                        'quotation_id',
                        'total',
                        'currency_id',
                        'quoted_on',
                        'start_date',
                        'end_date',
                        'trip_days',
                        'travelers_count',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_quotations_require_authentication(): void
    {
        $this
            ->getJson('/api/quotations')
            ->assertUnauthorized();
    }

    public function test_quotations_are_returned_newest_first(): void
    {
        $user = User::factory()->create();

        $older = Quotation::factory()
            ->for($user)
            ->create([
                'created_at' => now()->subDay(),
            ]);

        $newer = Quotation::factory()
            ->for($user)
            ->create([
                'created_at' => now(),
            ]);

        $token = JWTAuth::fromUser($user);

        $this
            ->withToken($token)
            ->getJson('/api/quotations')
            ->assertOk()
            ->assertJsonPath(
                'data.0.quotation_id',
                $newer->public_id,
            )
            ->assertJsonPath(
                'data.1.quotation_id',
                $older->public_id,
            );
    }
}
