<?php

declare(strict_types=1);

namespace Tests\Feature\Quotation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

final class QuotationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_fields_are_validated(): void
    {
        $this->authenticatedRequest([])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'currency_id',
                'start_date',
                'end_date',
                'travelers',
            ]);
    }

    public function test_currency_must_be_supported(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'currency_id' => 'CAD',
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'currency_id',
            ]);
    }

    public function test_end_date_cannot_precede_start_date(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'start_date' => '2026-10-30',
                'end_date' => '2026-10-01',
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'end_date',
            ]);
    }

    public function test_at_least_one_traveler_is_required(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'travelers' => [],
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'travelers',
            ]);
    }

    public function test_traveler_full_name_is_required(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'travelers' => [
                    [
                        'date_of_birth' => '1998-05-10',
                    ],
                ],
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'travelers.0.full_name',
            ]);
    }

    public function test_traveler_date_of_birth_is_required(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'travelers' => [
                    [
                        'full_name' => 'Pavel Darii',
                    ],
                ],
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'travelers.0.date_of_birth',
            ]);
    }

    public function test_underage_traveler_is_rejected(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'travelers' => [
                    [
                        'full_name' => 'Young Traveler',
                        'date_of_birth' => '2010-01-01',
                    ],
                ],
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'travelers.0.date_of_birth',
            ]);
    }

    public function test_traveler_over_seventy_is_rejected(): void
    {
        $this->authenticatedRequest(
            $this->validPayload([
                'travelers' => [
                    [
                        'full_name' => 'Older Traveler',
                        'date_of_birth' => '1950-01-01',
                    ],
                ],
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'travelers.0.date_of_birth',
            ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function authenticatedRequest(
        array $payload,
    ): TestResponse {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        return $this
            ->withToken($token)
            ->postJson('/api/quotation', $payload);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function validPayload(
        array $overrides = [],
    ): array {
        $payload = [
            'currency_id' => 'EUR',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-30',
            'travelers' => [
                [
                    'full_name' => 'Pavel Darii',
                    'date_of_birth' => '1998-05-10',
                ],
            ],
        ];

        return array_replace($payload, $overrides);
    }
}
