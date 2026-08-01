<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_jwt(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Pavel Darii',
            'email' => 'PAVEL@EXAMPLE.COM',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.name', 'Pavel Darii')
            ->assertJsonPath(
                'data.user.email',
                'pavel@example.com',
            )
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);

        $user = User::query()
            ->where('email', 'pavel@example.com')
            ->firstOrFail();

        self::assertTrue(
            Hash::check('Password123!', $user->password),
        );

        self::assertNotSame(
            'Password123!',
            $user->password,
        );
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'pavel@example.com',
        ]);

        $this->postJson('/api/auth/register', [
            'name' => 'Pavel Darii',
            'email' => 'pavel@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_confirmation_is_required(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Pavel Darii',
            'email' => 'pavel@example.com',
            'password' => 'Password123!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_weak_password_is_rejected(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Pavel Darii',
            'email' => 'pavel@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
